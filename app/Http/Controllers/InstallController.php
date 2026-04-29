<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Exception;

class InstallController extends Controller
{
    public function index()
    {
        // Ensure .env exists and APP_KEY is set BEFORE any forms are shown.
        // This prevents session/CSRF invalidation when the database step writes .env.
        $this->ensureEnvAndKey();

        $requirements = [
            'php' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'bcmath' => extension_loaded('bcmath'),
            'ctype' => extension_loaded('ctype'),
            'fileinfo' => extension_loaded('fileinfo'),
            'json' => extension_loaded('json'),
            'mbstring' => extension_loaded('mbstring'),
            'openssl' => extension_loaded('openssl'),
            'pdo' => extension_loaded('pdo'),
            'tokenizer' => extension_loaded('tokenizer'),
            'xml' => extension_loaded('xml'),
            'storage' => is_writable(storage_path()),
            'bootstrap' => is_writable(base_path('bootstrap/cache')),
        ];

        $pass = !in_array(false, $requirements, true);

        return view('installer.requirements', compact('requirements', 'pass'));
    }

    public function database()
    {
        return view('installer.database');
    }

    public function databaseSave(Request $request)
    {
        if (empty($request->db_database)) {
            $request->merge(['db_database' => 'polycms']);
        }

        $request->validate([
            'db_connection' => 'required|in:mysql,pgsql',
            'db_host' => 'required',
            'db_port' => 'required|numeric',
            'db_database' => 'nullable|string',
            'db_username' => 'required',
        ]);

        // Attempt connection safely
        try {
            $driver = $request->db_connection;
            $host = $request->db_host;
            $port = $request->db_port;
            $dbName = $request->db_database;
            $username = $request->db_username;
            $password = $request->db_password;

            // Step 1: Try connecting directly to the specified database
            try {
                $dsn = sprintf('%s:host=%s;port=%s;dbname=%s', $driver, $host, $port, $dbName);
                $pdo = new \PDO($dsn, $username, $password);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

                // Connected! Check if database has existing tables
                if ($driver === 'mysql') {
                    $tableCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?");
                    $tableCheck->execute([$dbName]);
                    $tableCount = $tableCheck->fetchColumn();
                    
                    if ($tableCount > 0 && !$request->has('force_overwrite')) {
                        return back()->with('db_exists_warning', true)->withInput();
                    }
                }
            } catch (\PDOException $directErr) {
                // Step 2: Direct connection failed — try connecting without database to create it
                try {
                    $dsnNoDB = sprintf('%s:host=%s;port=%s', $driver, $host, $port);
                    if ($driver === 'pgsql') {
                        $dsnNoDB .= ';dbname=postgres';
                    }
                    
                    $pdoAdmin = new \PDO($dsnNoDB, $username, $password);
                    $pdoAdmin->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

                    if ($driver === 'mysql') {
                        $safeName = "`" . str_replace("`", "``", $dbName) . "`";
                        $pdoAdmin->exec("CREATE DATABASE IF NOT EXISTS $safeName CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    } elseif ($driver === 'pgsql') {
                        $checkDb = $pdoAdmin->prepare("SELECT 1 FROM pg_database WHERE datname = ?");
                        $checkDb->execute([$dbName]);
                        if (!$checkDb->fetchColumn()) {
                            $pdoAdmin->exec("CREATE DATABASE \"" . str_replace('"', '""', $dbName) . "\"");
                        }
                    }

                    // Verify connection to the newly created database
                    $dsn = sprintf('%s:host=%s;port=%s;dbname=%s', $driver, $host, $port, $dbName);
                    $pdo = new \PDO($dsn, $username, $password);
                    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                } catch (\PDOException $createErr) {
                    // Both methods failed — show the original direct connection error
                    throw $directErr;
                }
            }

        } catch (Exception $e) {
            return back()->withErrors(['db_connection' => 'Connection or Database Creation failed: ' . $e->getMessage()])->withInput();
        }

        // Save DB config to .env (APP_KEY already set in Step 1 — do NOT regenerate here
        // or the session/CSRF token will be invalidated, causing the redirect to fail)
        $envData = [
            'DB_CONNECTION' => $request->db_connection,
            'DB_HOST' => $request->db_host,
            'DB_PORT' => $request->db_port,
            'DB_DATABASE' => $request->db_database,
            'DB_USERNAME' => $request->db_username,
            'DB_PASSWORD' => $request->db_password,
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'APP_URL' => url('/'),
        ];

        // Auto-enable HTTPS settings when installing over SSL
        if ($request->isSecure() || str_starts_with(url('/'), 'https://')) {
            $envData['FORCE_HTTPS'] = 'true';
            $envData['SESSION_SECURE_COOKIE'] = 'true';
        }

        $this->updateEnv($envData);

        return redirect()->route('install.migrations');
    }

    public function migrations()
    {
        return view('installer.migrations');
    }

    public function migrationsRun()
    {
        try {
            Artisan::call('key:generate', ['--force' => true]);
            Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
            
            // Reconnect DB so eloquent models use new config if we had to do it.
            // Normally Artisan command runs it, but in web context it might be cached.
            
            if (function_exists('symlink')) {
                Artisan::call('storage:link');
            }

            \Illuminate\Support\Facades\DB::table('themes')->updateOrInsert(
                ['slug' => 'flexiwhite'],
                [
                    'name' => 'FlexiWhite',
                    'version' => '1.0.0',
                    'author' => 'PolyCMS Team',
                    'description' => 'A clean, modern starter theme with responsive layout and dark mode support',
                    'type' => 'frontend',
                    'is_active' => true,
                    'status' => 'installed',
                    'path' => 'themes/flexiwhite',
                    'role' => 'main',
                    'template_registry' => json_encode([
                        "home" => ["name" => "FlexiWhite — Homepage"],
                        "posts.index" => ["name" => "FlexiWhite — Post Archive"],
                        "posts.show" => ["name" => "FlexiWhite — Single Post"],
                        "pages.show" => ["name" => "FlexiWhite — Single Page"],
                        "products.index" => ["name" => "FlexiWhite — Product Listing"],
                        "products.show" => ["name" => "FlexiWhite — Single Product"],
                        "categories.show" => ["name" => "FlexiWhite — Category Archive"],
                    ]),
                    'priority' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
            return response()->json(['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function admin()
    {
        return view('installer.admin');
    }

    public function adminSave(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Seeded admin
        $admin = User::firstWhere('email', 'admin@polycms.org') ?? User::first() ?? new User();
        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->password = Hash::make($request->password);
        $admin->email_verified_at = now();
        $admin->save();

        if (method_exists($admin, 'assignRole') && !$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        return redirect()->route('install.finish');
    }

    public function finish()
    {
        // Create storage symlink using PHP directly (not artisan)
        // This avoids issues with CLI PHP version mismatch on shared hosting
        $this->createStorageLink();

        file_put_contents(storage_path('installed.lock'), date('Y-m-d H:i:s'));

        return view('installer.finish');
    }

    /**
     * Create public/storage -> storage/app/public symlink.
     * Uses PHP symlink() directly instead of artisan to avoid CLI PHP version issues.
     */
    protected function createStorageLink()
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        // Ensure target directory exists
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }

        // Remove existing directory/symlink if present
        if (is_link($link)) {
            return; // Already a symlink, skip
        }

        if (is_dir($link)) {
            // Real directory exists (from deploy package) — remove it
            $this->removeDirectory($link);
        }

        // Create symlink
        try {
            symlink($target, $link);
        } catch (\Exception $e) {
            // Symlink not supported — create .htaccess redirect as fallback
        }
    }

    protected function removeDirectory($dir)
    {
        if (!is_dir($dir)) return;
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    protected function updateEnv($data)
    {
        $envFile = base_path('.env');
        if (!file_exists($envFile)) {
            copy(base_path('.env.example'), $envFile);
        }

        $envContent = file_get_contents($envFile);

        foreach ($data as $key => $value) {
            $value = (string) $value;
            if (preg_match('/^' . $key . '=/m', $envContent)) {
                $safeValue = str_contains($value, ' ') || str_contains($value, '#') ? '"' . $value . '"' : $value;
                $envContent = preg_replace('/^' . $key . '=.*/m', $key . '=' . $safeValue, $envContent);
            } else {
                $safeValue = str_contains($value, ' ') || str_contains($value, '#') ? '"' . $value . '"' : $value;
                $envContent .= "\n" . $key . '=' . $safeValue;
            }
        }

        file_put_contents($envFile, $envContent);
    }

    /**
     * Ensure .env file exists and APP_KEY is set.
     * Must be called BEFORE any form is shown so the session key is stable.
     */
    protected function ensureEnvAndKey()
    {
        $envFile = base_path('.env');

        // Create .env from example if missing
        if (!file_exists($envFile)) {
            copy(base_path('.env.example'), $envFile);
        }

        // Generate APP_KEY if empty
        $envContent = file_get_contents($envFile);
        if (preg_match('/^APP_KEY=\s*$/m', $envContent) || !preg_match('/^APP_KEY=/m', $envContent)) {
            $key = 'base64:' . base64_encode(\Illuminate\Support\Str::random(32));
            if (preg_match('/^APP_KEY=/m', $envContent)) {
                $envContent = preg_replace('/^APP_KEY=.*/m', 'APP_KEY=' . $key, $envContent);
            } else {
                $envContent .= "\nAPP_KEY=" . $key;
            }
            file_put_contents($envFile, $envContent);

            // Reload config so the new key takes effect immediately
            config(['app.key' => $key]);
        }
    }
}
