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

    <div class="min-h-screen bg-white py-10">

        <div class="max-w-7xl mx-auto px-4">

            <!-- Balance Card -->
            <div
                class="bg-gradient-to-r from-indigo-500 to-purple-500 rounded-3xl p-8 text-white shadow-2xl mb-8">

                <p class="uppercase tracking-widest text-sm opacity-80">
                    Current Balance
                </p>

                <h1 class="text-5xl font-black mt-3">
                    ₹{{ number_format($wallet?->balance ?? 0, 2) }}
                </h1>

                <div class="mt-6 flex gap-4">

                    <div class="bg-white/20 px-4 py-2 rounded-xl text-sm">
                        👤 {{ auth()->user()->email }}
                    </div>

                    <div class="bg-white/20 px-4 py-2 rounded-xl text-sm">
                        📅 {{ now()->format('d M Y') }}
                    </div>

                </div>

            </div>

            <!-- Alerts -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-300 text-green-700 p-4 rounded-2xl mb-5">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-300 text-red-700 p-4 rounded-2xl mb-5">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Cards -->
            <div class="grid lg:grid-cols-3 gap-6">

                <!-- Add Money -->
                <div class="bg-white border border-gray-200 rounded-3xl p-6 shadow-lg">

                    <div class="flex justify-between items-center mb-5">

                        <h3 class="text-xl font-bold text-blue-600">
                            ➕ Add Money
                        </h3>

                        <div class="bg-blue-100 p-3 rounded-2xl">
                            💰
                        </div>

                    </div>

                    <form method="POST" action="{{ route('wallet.add') }}">
                        @csrf

                        <input type="number"
                            name="amount"
                            placeholder="Enter amount"
                            class="w-full rounded-2xl border-gray-300 mb-4">

                        <button
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-2xl font-bold transition">
                            Add Money
                        </button>

                    </form>

                </div>

                <!-- Deduct -->
                <div class="bg-white border border-gray-200 rounded-3xl p-6 shadow-lg">

                    <div class="flex justify-between items-center mb-5">

                        <h3 class="text-xl font-bold text-red-600">
                            ➖ Deduct Money
                        </h3>

                        <div class="bg-red-100 p-3 rounded-2xl">
                            💸
                        </div>

                    </div>

                    <form method="POST" action="{{ route('wallet.deduct') }}">
                        @csrf

                        <input type="number"
                            name="amount"
                            placeholder="Enter amount"
                            class="w-full rounded-2xl border-gray-300 mb-4">

                        <button
                            class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-2xl font-bold transition">
                            Deduct Money
                        </button>

                    </form>

                </div>

                <!-- Transfer -->
                <div class="bg-white border border-gray-200 rounded-3xl p-6 shadow-lg">

                    <div class="flex justify-between items-center mb-5">

                        <h3 class="text-xl font-bold text-green-600">
                            🔁 Transfer Money
                        </h3>

                        <div class="bg-green-100 p-3 rounded-2xl">
                            🚀
                        </div>

                    </div>

                    <form method="POST" action="{{ route('wallet.transfer') }}">
                        @csrf

                        <input type="email"
                            name="email"
                            placeholder="Receiver Email"
                            class="w-full rounded-2xl border-gray-300 mb-4">

                        <input type="number"
                            name="amount"
                            placeholder="Enter amount"
                            class="w-full rounded-2xl border-gray-300 mb-4">

                        <button
                            class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-2xl font-bold transition">
                            Send Money
                        </button>

                    </form>

                </div>

            </div>

            <!-- Search -->
            <div class="bg-white border border-gray-200 rounded-3xl p-6 shadow-lg mt-8">

                <form method="GET"
                    action="{{ route('wallet') }}"
                    class="grid md:grid-cols-3 gap-4">

                    <input type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="🔍 Search transaction..."
                        class="rounded-2xl border-gray-300">

                    <select name="type"
                        class="rounded-2xl border-gray-300">

                        <option value="">All Types</option>

                        <option value="credit"
                            {{ request('type') == 'credit' ? 'selected' : '' }}>
                            Credit
                        </option>

                        <option value="debit"
                            {{ request('type') == 'debit' ? 'selected' : '' }}>
                            Debit
                        </option>

                    </select>

                    <button
                        class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-bold py-3">
                        Search
                    </button>

                </form>

            </div>

            <!-- Transactions -->
            <div class="bg-white border border-gray-200 rounded-3xl p-6 shadow-lg mt-8">

                <div class="flex justify-between items-center mb-6">

                    <div>
                        <h3 class="text-2xl font-bold text-gray-800">
                            📜 Recent Transactions
                        </h3>

                        <p class="text-gray-500 text-sm">
                            Wallet activity history
                        </p>
                    </div>

                    <div class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-2xl font-semibold">
                        Total: {{ $transactions->total() }}
                    </div>

                </div>

                @forelse($transactions as $txn)

                    <div
                        class="flex justify-between items-center border border-gray-100 hover:shadow-md transition rounded-2xl p-5 mb-4">

                        <div class="flex items-center gap-4">

                            <div
                                class="w-14 h-14 rounded-2xl flex items-center justify-center text-white text-xl
                                {{ $txn->type == 'credit'
                                    ? 'bg-green-500'
                                    : 'bg-red-500' }}">

                                {{ $txn->type == 'credit' ? '+' : '-' }}

                            </div>

                            <div>

                                <h4 class="font-bold text-lg capitalize text-gray-800">
                                    {{ $txn->type }}
                                </h4>

                                <p class="text-gray-500 text-sm">
                                    {{ $txn->description }}
                                </p>

                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $txn->created_at->format('d M Y h:i A') }}
                                </p>

                            </div>

                        </div>

                        <div class="text-right">

                            <h3
                                class="text-2xl font-black
                                {{ $txn->type == 'credit'
                                    ? 'text-green-600'
                                    : 'text-red-600' }}">

                                {{ $txn->type == 'credit' ? '+' : '-' }}
                                ₹{{ $txn->amount }}

                            </h3>

                            <form method="POST"
                                action="{{ route('transaction.delete', $txn->id) }}"
                                class="mt-3">

                                @csrf
                                @method('DELETE')

                                <button
                                    onclick="return confirm('Delete transaction?')"
                                    class="bg-red-100 hover:bg-red-200 text-red-600 px-4 py-1 rounded-xl text-sm font-medium transition">
                                    Delete
                                </button>

                            </form>

                        </div>

                    </div>

                @empty

                    <div class="text-center py-16">

                        <div class="text-7xl mb-4">
                            📭
                        </div>

                        <h3 class="text-2xl font-bold text-gray-700">
                            No Transactions Found
                        </h3>

                    </div>

                @endforelse

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $transactions->links() }}
                </div>

            </div>

        </div>

    </div>

</x-app-layout>