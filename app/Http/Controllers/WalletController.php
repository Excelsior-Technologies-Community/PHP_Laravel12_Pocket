<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Wallet;

class WalletController extends Controller
{
    private $rates = [
        'INR' => 1.0,
        'USD' => 0.012,
        'EUR' => 0.011
    ];

    public function index(Request $request)
    {
        $user = Auth::user();

        $wallet = $user->wallet()->firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'currency' => 'INR']
        );

        $search = $request->search;
        $type = $request->type;
        $category = $request->category;

        $transactions = $user->transactions()
            ->when($search, function ($query) use ($search) {
                $query->where('description', 'like', "%{$search}%");
            })
            ->when($type, function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->when($category, function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $currentMonth = now()->format('Y-m');
        $budget = Budget::where('user_id', $user->id)->where('month_year', $currentMonth)->first();
        
        $totalExpense = Transaction::where('user_id', $user->id)
            ->where('type', 'debit')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $budgetExceeded = false;
        if ($budget && $totalExpense > $budget->amount_limit) {
            $budgetExceeded = true;
        }

        $chartData = Transaction::where('user_id', $user->id)
            ->where('type', 'debit')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();

        return view('dashboard', compact(
            'wallet',
            'transactions',
            'search',
            'type',
            'category',
            'budget',
            'totalExpense',
            'budgetExceeded',
            'chartData'
        ));
    }

    public function addMoney(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:99999999',
            'mock_card_number' => 'required|numeric|digits:16'
        ]);

        DB::transaction(function () use ($request) {
            $user = Auth::user();
            $wallet = $user->wallet()->firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'currency' => 'INR']
            );

            $wallet->increment('balance', $request->amount);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $request->amount,
                'currency' => $wallet->currency,
                'category' => 'Deposit',
                'description' => 'Money loaded via mock card ending in ' . substr($request->mock_card_number, -4)
            ]);
        });

        return back()->with('success', 'Money added successfully!');
    }

    public function deductMoney(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:99999999',
            'category' => 'required|string'
        ]);

        $user = Auth::user();
        $wallet = $user->wallet()->firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'currency' => 'INR']
        );

        if ($wallet->balance < $request->amount) {
            return back()->with('error', 'Insufficient balance!');
        }

        DB::transaction(function () use ($request, $wallet, $user) {
            $wallet->decrement('balance', $request->amount);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $request->amount,
                'currency' => $wallet->currency,
                'category' => $request->category,
                'description' => 'Money spent on ' . $request->category
            ]);
        });

        return back()->with('success', 'Money deducted successfully!');
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'email' => 'required|exists:users,email',
            'amount' => 'required|numeric|min:1|max:99999999'
        ]);

        $sender = Auth::user();
        $receiver = User::where('email', $request->email)->first();

        if ($sender->id === $receiver->id) {
            return back()->with('error', 'You cannot transfer money to yourself.');
        }

        DB::beginTransaction();

        try {
            $senderWallet = $sender->wallet()->lockForUpdate()->first();
            $receiverWallet = $receiver->wallet()->lockForUpdate()->first();

            if (!$receiverWallet) {
                $receiverWallet = $receiver->wallet()->create([
                    'balance' => 0,
                    'currency' => 'INR'
                ]);
            }

            if ($senderWallet->balance < $request->amount) {
                return back()->with('error', 'Insufficient balance!');
            }

            $amountInBase = $request->amount / $this->rates[$senderWallet->currency];
            $amountInReceiver = $amountInBase * $this->rates[$receiverWallet->currency];

            $senderWallet->decrement('balance', $request->amount);
            $receiverWallet->increment('balance', $amountInReceiver);

            Transaction::create([
                'user_id' => $sender->id,
                'type' => 'debit',
                'amount' => $request->amount,
                'currency' => $senderWallet->currency,
                'category' => 'Transfer',
                'description' => 'Sent to ' . $receiver->email
            ]);

            Transaction::create([
                'user_id' => $receiver->id,
                'type' => 'credit',
                'amount' => $amountInReceiver,
                'currency' => $receiverWallet->currency,
                'category' => 'Transfer',
                'description' => 'Received from ' . $sender->email
            ]);

            DB::commit();
            return back()->with('success', 'Money transferred successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Transfer failed!');
        }
    }

    public function changeCurrency(Request $request)
    {
        $request->validate([
            'currency' => 'required|in:INR,USD,EUR'
        ]);

        $user = Auth::user();
        $wallet = $user->wallet()->first();

        if ($wallet && $wallet->currency !== $request->currency) {
            DB::transaction(function () use ($wallet, $request) {
                $oldCurrency = $wallet->currency;
                $newCurrency = $request->currency;

                $balanceInBase = $wallet->balance / $this->rates[$oldCurrency];
                $newBalance = $balanceInBase * $this->rates[$newCurrency];

                $wallet->update([
                    'balance' => $newBalance,
                    'currency' => $newCurrency
                ]);
            });
        }

        return back()->with('success', 'Currency changed successfully!');
    }

    public function setBudget(Request $request)
    {
        $request->validate([
            'amount_limit' => 'required|numeric|min:1|max:99999999'
        ]);

        $user = Auth::user();
        $currentMonth = now()->format('Y-m');

        Budget::updateOrCreate(
            ['user_id' => $user->id, 'month_year' => $currentMonth],
            ['amount_limit' => $request->amount_limit]
        );

        return back()->with('success', 'Monthly budget limit updated!');
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id != Auth::id()) {
            abort(403);
        }

        $transaction->delete();
        return back()->with('success', 'Transaction deleted successfully!');
    }
}