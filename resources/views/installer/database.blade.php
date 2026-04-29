@extends('installer.layout')

@section('content')
<h2 class="text-xl font-semibold mb-4 text-gray-800">Database Configuration</h2>
<p class="text-sm text-gray-500 mb-6">Enter your database connection details below.</p>

@if ($errors->any())
    <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 text-sm border border-red-100">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        @if (collect($errors->all())->contains(fn($e) => str_contains($e, 'Access denied')))
            <div class="mt-3 pt-3 border-t border-red-100 text-red-500">
                <p class="font-semibold mb-1">Common fix:</p>
                <p>If your MySQL user was created with host <code>%</code>, it may not match <code>localhost</code> connections. Run this SQL in phpMyAdmin:</p>
                <div class="relative mt-2">
                    <textarea
                        readonly
                        onclick="this.select()"
                        class="w-full bg-gray-50 text-gray-800 text-xs font-mono p-3 rounded border border-gray-200 resize-none focus:outline-none focus:border-blue-300"
                        rows="3"
                        style="line-height: 1.6;"
                    >CREATE USER 'your_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON your_database.* TO 'your_user'@'localhost';
FLUSH PRIVILEGES;</textarea>
                    <button
                        type="button"
                        onclick="const ta = this.previousElementSibling; ta.select(); document.execCommand('copy'); this.textContent = '✓ Copied'; setTimeout(() => this.textContent = 'Copy', 1500);"
                        class="absolute top-2 right-2 bg-white text-gray-500 hover:text-gray-700 text-xs px-2 py-1 rounded border border-gray-200 hover:border-gray-300 transition-colors"
                    >Copy</button>
                </div>
            </div>
        @endif
    </div>
@endif

<form action="{{ route('install.database.save') }}" method="POST">
    @csrf
    
    <div class="space-y-4 mb-8">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Database Connection</label>
            <select name="db_connection" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2.5">
                <option value="mysql" {{ old('db_connection', 'mysql') === 'mysql' ? 'selected' : '' }}>MySQL / MariaDB</option>
                <option value="pgsql" {{ old('db_connection') === 'pgsql' ? 'selected' : '' }}>PostgreSQL</option>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Host</label>
                <input type="text" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2.5">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
                <input type="number" name="db_port" value="{{ old('db_port', '3306') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2.5">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Database Name</label>
            <input type="text" name="db_database" placeholder="polycms" value="{{ old('db_database', 'polycms') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2.5">
            <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                <strong>Option 1 (Recommended):</strong> Leave the default name to auto-create a brand new database for this installation.<br>
                <strong>Option 2:</strong> Enter your own custom database name if you have manually created one beforehand. If the database already exists, the system will warn you before any existing data is overwritten.
            </p>
        </div>

        @if(session('db_exists_warning'))
            <div class="bg-amber-50 text-amber-800 p-4 rounded-lg mt-4 border border-amber-200">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-amber-600 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <div>
                        <h4 class="text-sm font-semibold">Database Already Exists</h4>
                        <p class="text-sm mt-1">The expected database <span class="font-bold">`{{ old('db_database', 'polycms') }}`</span> already exists. Proceeding will migrate and may overwrite existing tables. Are you sure you want to proceed?</p>
                        <div class="mt-3">
                            <input type="hidden" name="force_overwrite" value="1">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="confirm_overwrite" required class="rounded border-amber-300 text-amber-600 shadow-sm focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-amber-800 font-medium">Yes, I confirm holding the risk and overwriting this database</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input type="text" name="db_username" value="{{ old('db_username', 'root') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2.5">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="db_password" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2.5">
            <span class="text-xs text-gray-500 mt-1 block">Leave empty if no password is required.</span>
        </div>
    </div>

    <div class="flex justify-between items-center mt-6">
        <a href="{{ route('install.index') }}" class="text-sm text-gray-500 hover:text-gray-900 transition-colors">Back</a>
        <div class="flex items-center gap-4">
            <div class="text-sm text-gray-500">Step 2 of 4</div>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors inline-flex items-center gap-2">
                Save & Continue
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
        </div>
    </div>
</form>

<script>
    document.querySelector('select[name="db_connection"]').addEventListener('change', function(e) {
        if(e.target.value === 'pgsql') {
            document.querySelector('input[name="db_port"]').value = '5432';
        } else {
            document.querySelector('input[name="db_port"]').value = '3306';
        }
    });
</script>
@endsection
