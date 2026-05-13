<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Transaction;

class WalletController extends Controller
{
    /**
     * Show wallet dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ensure wallet exists
        $wallet = $user->wallet()->firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        // Search + Filter
        $search = $request->search;
        $type = $request->type;

        $transactions = $user->transactions()
            ->when($search, function ($query) use ($search) {
                $query->where('description', 'like', "%{$search}%");
            })
            ->when($type, function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->oldest()
            ->paginate(2)
            ->withQueryString();

        return view('dashboard', compact(
            'wallet',
            'transactions',
            'search',
            'type'
        ));
    }
    /**
     * Add money to wallet
     */
    public function addMoney(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        DB::transaction(function () use ($request) {

            $user = Auth::user();

            //  Ensure wallet exists
            $wallet = $user->wallet()->firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0]
            );

            $wallet->increment('balance', $request->amount);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $request->amount,
                'description' => 'Money added'
            ]);
        });

        return back()->with('success', 'Money added successfully!');
    }

    /**
     * Deduct money from wallet
     */
    public function deductMoney(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $user = Auth::user();

        //  Ensure wallet exists
        $wallet = $user->wallet()->firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        //  Check insufficient balance
        if ($wallet->balance < $request->amount) {
            return back()->with('error', 'Insufficient balance!');
        }

        DB::transaction(function () use ($request, $wallet, $user) {

            $wallet->decrement('balance', $request->amount);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $request->amount,
                'description' => 'Money deducted'
            ]);
        });

        return back()->with('success', 'Money deducted successfully!');
    }

    /**
     * Transfer money between users
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'email' => 'required|exists:users,email',
            'amount' => 'required|numeric|min:1'
        ]);

        $sender = Auth::user();
        $receiver = User::where('email', $request->email)->first();

        //  Prevent self transfer
        if ($sender->id === $receiver->id) {
            return back()->with('error', 'You cannot transfer money to yourself.');
        }

        DB::beginTransaction();

        try {
            $senderWallet = $sender->wallet()->lockForUpdate()->first();

            if (!$senderWallet) {
                return back()->with('error', 'Sender wallet not found!');
            }

            // Create receiver wallet if not exists
            $receiverWallet = $receiver->wallet()->lockForUpdate()->first();

            if (!$receiverWallet) {
                $receiverWallet = $receiver->wallet()->create([
                    'balance' => 0
                ]);
            }

            if ($senderWallet->balance < $request->amount) {
                return back()->with('error', 'Insufficient balance!');
            }

            // Deduct from sender
            $senderWallet->decrement('balance', $request->amount);

            // Add to receiver
            $receiverWallet->increment('balance', $request->amount);

            // Transactions
            Transaction::create([
                'user_id' => $sender->id,
                'type' => 'debit',
                'amount' => $request->amount,
                'description' => 'Sent to ' . $receiver->email
            ]);

            Transaction::create([
                'user_id' => $receiver->id,
                'type' => 'credit',
                'amount' => $request->amount,
                'description' => 'Received from ' . $sender->email
            ]);

            DB::commit();

            return back()->with('success', 'Money transferred successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Transfer failed!');
        }
    }

    public function destroy(Transaction $transaction)
    {
        // Security check
        if ($transaction->user_id != Auth::id()) {
            abort(403);
        }

        $transaction->delete();

        return back()->with('success', 'Transaction deleted successfully!');
    }
}