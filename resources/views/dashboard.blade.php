<x-app-layout>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-3xl font-extrabold text-gray-800">
                💳 Wallet Dashboard
            </h2>
            <div class="bg-indigo-600 text-white px-5 py-2 rounded-2xl shadow">
                {{ auth()->user()->name }}
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gray-50 py-10">
        <div class="max-w-7xl mx-auto px-4">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-2xl mb-6 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error') || $budgetExceeded)
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-2xl mb-6 shadow-sm">
                    {{ session('error') ?? 'Alert: Your monthly budget limit has been exceeded!' }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                
                <div class="bg-gradient-to-r from-indigo-500 to-purple-500 rounded-3xl p-8 text-white shadow-2xl flex flex-col justify-between">
                    <div>
                        <p class="uppercase tracking-widest text-sm opacity-80">Current Balance</p>
                        <h1 class="text-5xl font-black mt-3">
                            @if(($wallet?->currency ?? 'INR') == 'USD') $ @elseif(($wallet?->currency ?? 'INR') == 'EUR') € @else ₹ @endif{{ number_format($wallet?->balance ?? 0, 2) }}
                        </h1>
                    </div>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <div class="bg-white/20 px-3 py-1.5 rounded-xl text-xs">👤 {{ auth()->user()->email }}</div>
                        <div class="bg-white/20 px-3 py-1.5 rounded-xl text-xs">📅 {{ now()->format('d M Y') }}</div>
                        <div class="bg-white/20 px-3 py-1.5 rounded-xl text-xs">💱 {{ $wallet?->currency ?? 'INR' }}</div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-xl border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">⚙️ Wallet Controls</h3>
                    <div class="space-y-4">
                        <form method="POST" action="{{ route('wallet.currency') }}">
                            @csrf
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Change Currency</label>
                            <div class="flex gap-2">
                                <select name="currency" class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="INR" {{ ($wallet?->currency ?? 'INR') == 'INR' ? 'selected' : '' }}>INR (₹)</option>
                                    <option value="USD" {{ ($wallet?->currency ?? 'INR') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                    <option value="EUR" {{ ($wallet?->currency ?? 'INR') == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                </select>
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition shadow-sm">Apply</button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('wallet.budget') }}">
                            @csrf
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Set Monthly Budget ({{ $wallet?->currency ?? 'INR' }})</label>
                            <div class="flex gap-2">
                                <input type="number" name="amount_limit" value="{{ $budget?->amount_limit ?? '' }}" placeholder="Limit amount" class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition shadow-sm">Set</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-xl border border-gray-100 flex flex-col justify-center items-center">
                    <h3 class="text-lg font-bold text-gray-800 mb-2 w-full text-left">📊 Expense Chart</h3>
                    <div class="w-full h-40 flex items-center justify-center">
                        <canvas id="expenseChart"></canvas>
                    </div>
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
                
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">📥 Add Money (Mock Card)</h3>
                    <form method="POST" action="{{ route('wallet.add') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Amount</label>
                            <input type="number" name="amount" min="1" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Dummy Card Number (16 Digits)</label>
                            <input type="text" name="mock_card_number" maxlength="16" required placeholder="4111 2222 3333 4444" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl text-sm font-semibold transition shadow-lg">Load Wallet</button>
                    </form>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-xl border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">💸 Expense Deduct</h3>
                    <form method="POST" action="{{ route('wallet.deduct') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Amount</label>
                            <input type="number" name="amount" min="1" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Category</label>
                            <select name="category" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="Food">Food & Drinks</option>
                                <option value="Shopping">Shopping</option>
                                <option value="Bills">Utility Bills</option>
                                <option value="Travel">Travel & Fuel</option>
                                <option value="Entertainment">Entertainment</option>
                                <option value="Other">Other Expense</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-xl text-sm font-semibold transition shadow-lg">Spend Money</button>
                    </form>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-xl border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">🔄 P2P Money Transfer</h3>
                    <form method="POST" action="{{ route('wallet.transfer') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Receiver Email</label>
                            <input type="email" name="email" required placeholder="user@example.com" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Amount</label>
                            <input type="number" name="amount" min="1" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white py-3 rounded-xl text-sm font-semibold transition shadow-lg">Transfer Instantly</button>
                    </form>
                </div>

            </div>

            <div class="bg-white rounded-3xl p-6 shadow-xl border border-gray-100 mb-6">
                <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Search Description</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search here..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Filter Type</label>
                        <select name="type" class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none">
                            <option value="">All Types</option>
                            <option value="credit" {{ $type == 'credit' ? 'selected' : '' }}>Credit</option>
                            <option value="debit" {{ $type == 'debit' ? 'selected' : '' }}>Debit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Category</label>
                        <select name="category" class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none">
                            <option value="">All Categories</option>
                            <option value="Deposit" {{ $category == 'Deposit' ? 'selected' : '' }}>Deposit</option>
                            <option value="Transfer" {{ $category == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                            <option value="Food" {{ $category == 'Food' ? 'selected' : '' }}>Food</option>
                            <option value="Shopping" {{ $category == 'Shopping' ? 'selected' : '' }}>Shopping</option>
                            <option value="Bills" {{ $category == 'Bills' ? 'selected' : '' }}>Bills</option>
                            <option value="Travel" {{ $category == 'Travel' ? 'selected' : '' }}>Travel</option>
                            <option value="Entertainment" {{ $category == 'Entertainment' ? 'selected' : '' }}>Entertainment</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-xl text-sm font-medium transition">Filter</button>
                        <a href="{{ route('dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-xl text-sm font-medium transition">Reset</a>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100">
                <h3 class="text-2xl font-black text-gray-800 mb-6">📜 Passbook / Statement</h3>
                <div class="space-y-4">
                    @forelse($transactions as $txn)
                        <div class="flex justify-between items-center bg-gray-50 rounded-2xl p-4 border border-gray-100 hover:shadow-md transition">
                            <div class="flex items-center gap-4">
                                <div class="text-3xl p-2.5 rounded-xl bg-white shadow-sm">
                                    @if($txn->type == 'credit') 🟢 @else 🔴 @endif
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800">{{ $txn->description }}</h4>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        <span class="bg-gray-200 text-gray-700 px-1.5 py-0.5 rounded-md font-medium mr-1">{{ $txn->category ?? 'General' }}</span>
                                        {{ $txn->created_at->format('d M Y, h:i A') }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right flex flex-col items-end">
                                <h3 class="text-lg font-black {{ $txn->type == 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $txn->type == 'credit' ? '+' : '-' }}@if($txn->currency == 'USD')$@elseif($txn->currency == 'EUR')€@else₹@endif{{ number_format($txn->amount, 2) }}
                                </h3>
                                <form method="POST" action="{{ route('transaction.delete', $txn->id) }}" class="mt-1">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Delete transaction statement?')" class="text-xs text-red-400 hover:text-red-600 transition">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="text-5xl mb-3">📭</div>
                            <h3 class="text-xl font-bold text-gray-500">No Transactions Recorded</h3>
                        </div>
                    @endforelse
                </div>

                <div class="mt-6">
                    {{ $transactions->links() }}
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('expenseChart');
            if (ctx) {
                const chartLabels = {!! json_encode($chartData->pluck('category')->toArray()) !!};
                const chartValues = {!! json_encode($chartData->pluck('total')->toArray()) !!};

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: chartLabels.length ? chartLabels : ['No Expenses'],
                        datasets: [{
                            data: chartValues.length ? chartValues : [1],
                            backgroundColor: ['#f87171', '#60a5fa', '#fbbf24', '#34d399', '#a78bfa', '#fb7185', '#cbd5e1']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
        });
    </script>

</x-app-layout>