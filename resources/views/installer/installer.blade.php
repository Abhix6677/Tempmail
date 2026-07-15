<div class="w-full">

    @if($current === 0)

    <div class="text-center mb-6">
        <img src="{{ asset('images/logo.png') }}" class="mx-auto w-20 mb-3">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            TMail Installer
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Step 1 of 3 — Database Setup
        </p>
    </div>

    <form wire:submit.prevent="save" class="space-y-6">

        @if($state['db']['connection'] === 'sqlite')

            <div class="bg-green-100 dark:bg-green-800 border border-green-300 dark:border-green-700 rounded-lg p-4 text-green-800 dark:text-green-100">
                ✅ No configuration needed — using built‑in SQLite database.
            </div>

            <div>
                <button type="button"
                        wire:click="$set('state.db.connection', 'mysql')"
                        class="text-indigo-600 dark:text-indigo-400 text-sm hover:underline">
                    Use external database instead
                </button>
            </div>

        @else

            <div>
                <label class="block text-sm font-medium mb-1">Connection</label>
                <select wire:model="state.db.connection"
                        class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded p-2">
                    <option value="mysql">MySQL</option>
                    <option value="pgsql">PostgreSQL</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Hostname</label>
                <input wire:model="state.db.host"
                       class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded p-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Port</label>
                <input wire:model="state.db.port"
                       class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded p-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Database</label>
                <input wire:model="state.db.database"
                       class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded p-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Username</label>
                <input wire:model="state.db.username"
                       class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded p-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password"
                       wire:model="state.db.password"
                       class="w-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded p-2">
            </div>

            <div>
                <button type="button"
                        wire:click="$set('state.db.connection', 'sqlite')"
                        class="text-indigo-600 dark:text-indigo-400 text-sm hover:underline">
                    Use built-in SQLite instead
                </button>
            </div>

        @endif

        @if($error)
            <div class="bg-red-100 dark:bg-red-800 border border-red-300 dark:border-red-700 text-red-800 dark:text-red-100 p-3 rounded text-sm">
                {{ $error }}
            </div>
        @endif

        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded font-medium transition">
            Save & Next →
        </button>

    </form>

    @elseif($current === 1)

        <div class="text-center space-y-4">
            <h2 class="text-xl font-semibold">Database Installed Successfully ✅</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Your SQLite database has been configured and all migrations have completed.
            </p>

            <a href="{{ url('/') }}"
               class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded font-medium transition">
                Go to Application →
            </a>
        </div>

    @endif
</div>
