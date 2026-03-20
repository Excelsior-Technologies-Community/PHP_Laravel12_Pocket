<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800">
            💰 Wallet Dashboard
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-100 min-h-screen">

        <div class="max-w-4xl mx-auto px-4">

            <!-- Wallet Balance -->
            <div class="bg-gradient-to-r from-indigo-600 to-blue-500 text-white p-6 rounded-2xl shadow-lg mb-6">
                <p class="text-sm opacity-80">Wallet Balance</p>
                <h1 class="text-4xl font-bold">₹{{ $wallet?->balance ?? 0 }}</h1>
            </div>

            <!-- Messages -->
            @if(session('success'))
                <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Forms Grid -->
            <div class="grid md:grid-cols-3 gap-6">

                <!-- Add Money -->
                <div class="bg-white p-5 rounded-xl shadow">
                    <h3 class="font-semibold text-blue-600 mb-3">➕ Add Money</h3>

                    <form method="POST" action="/add-money">
                        @csrf

                        <input type="number" name="amount" placeholder="Enter amount"
                            class="w-full border p-2 rounded mb-3 focus:ring-2 focus:ring-blue-400">

                        <!-- IMPORTANT: FULL WIDTH BUTTON -->
                        <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold">
                            Add Money
                        </button>
                    </form>
                </div>

                <!-- Deduct -->
                <div class="bg-white p-5 rounded-xl shadow">
                    <h3 class="font-semibold text-red-600 mb-3">➖ Deduct</h3>

                    <form method="POST" action="/deduct-money">
                        @csrf

                        <input type="number" name="amount" placeholder="Enter amount"
                            class="w-full border p-2 rounded mb-3 focus:ring-2 focus:ring-red-400">

                        <button class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg font-semibold">
                            Deduct
                        </button>
                    </form>
                </div>

                <!-- Transfer -->
                <div class="bg-white p-5 rounded-xl shadow">
                    <h3 class="font-semibold text-green-600 mb-3">🔁 Transfer</h3>

                    <form method="POST" action="/transfer">
                        @csrf

                        <input type="email" name="email" placeholder="Receiver Email"
                            class="w-full border p-2 rounded mb-3 focus:ring-2 focus:ring-green-400">

                        <input type="number" name="amount" placeholder="Amount"
                            class="w-full border p-2 rounded mb-3 focus:ring-2 focus:ring-green-400">

                        <button class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-semibold">
                            Send Money
                        </button>
                    </form>
                </div>

            </div>

            <!-- Transactions -->
            <div class="bg-white p-6 rounded-xl shadow mt-8">
                <h3 class="font-semibold text-lg mb-4">📜 Transactions</h3>

                @forelse($transactions as $txn)
                    <div class="flex justify-between border-b py-3">

                        <div>
                            <p class="font-medium capitalize">{{ $txn->type }}</p>
                            <p class="text-sm text-gray-500">{{ $txn->description }}</p>
                        </div>

                        <div class="font-bold
                            {{ $txn->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $txn->type === 'credit' ? '+' : '-' }}₹{{ $txn->amount }}
                        </div>

                    </div>
                @empty
                    <p class="text-gray-500">No transactions yet.</p>
                @endforelse

            </div>

        </div>
    </div>
</x-app-layout>