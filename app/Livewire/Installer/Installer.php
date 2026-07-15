<?php

namespace App\Livewire\Installer;

use Livewire\Component;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Installer extends Component
{
    public $state = [
        'db' => [
            'connection' => 'sqlite',
            'host' => '',
            'port' => '',
            'database' => '',
            'username' => '',
            'password' => ''
        ],
    ];

    public $current = 0;
    public $error = '';
    public $success = '';

    public function mount()
    {
        $this->state['db']['connection'] = 'sqlite';
    }

    public function save()
    {
        $this->error = '';
        $this->success = '';

        try {
            if ($this->state['db']['connection'] === 'sqlite') {
                $databasePath = $this->setupSQLite();

                // Force runtime DB config (do NOT rely only on .env)
                config([
                    'database.default' => 'sqlite',
                    'database.connections.sqlite.database' => $databasePath,
                ]);

                DB::purge('sqlite');
                DB::reconnect('sqlite');
            } else {
                $this->validate([
                    'state.db.host' => 'required',
                    'state.db.port' => 'required|numeric',
                    'state.db.database' => 'required',
                    'state.db.username' => 'required',
                ]);

                // Do NOT modify .env during installer
                // Configure database connection at runtime only

                config([
                    'database.default' => $this->state['db']['connection'],
                    'database.connections.' . $this->state['db']['connection'] . '.host' => $this->state['db']['host'],
                    'database.connections.' . $this->state['db']['connection'] . '.port' => $this->state['db']['port'],
                    'database.connections.' . $this->state['db']['connection'] . '.database' => $this->state['db']['database'],
                    'database.connections.' . $this->state['db']['connection'] . '.username' => $this->state['db']['username'],
                    'database.connections.' . $this->state['db']['connection'] . '.password' => $this->state['db']['password'],
                ]);

                DB::purge($this->state['db']['connection']);
                DB::reconnect($this->state['db']['connection']);
            }

            // Run fresh migrations safely
            Artisan::call('migrate:fresh', ['--force' => true]);
            
            // Run seeders to populate required data
            Artisan::call('db:seed', ['--force' => true]);

            // Create installed file to mark installation complete
            File::put(storage_path('installed'), date('Y-m-d H:i:s'));

            $this->success = 'Database ready. Moving to next step.';
            $this->current = 1;
        } catch (\Throwable $e) {
            Log::error('Installer Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            $this->error = 'Installation failed: ' . $e->getMessage();
        }
    }

    private function setupSQLite()
    {
        $databaseDirectory = database_path();
        $databasePath = database_path('database.sqlite');

        if (!File::exists($databaseDirectory)) {
            File::makeDirectory($databaseDirectory, 0755, true);
        }

        // If file exists but is corrupted, delete it
        if (File::exists($databasePath)) {
            try {
                config([
                    'database.connections.sqlite.database' => $databasePath,
                ]);

                DB::purge('sqlite');
                DB::reconnect('sqlite');

                DB::connection('sqlite')->getPdo();
            } catch (\Throwable $e) {
                File::delete($databasePath);
            }
        }

        if (!File::exists($databasePath)) {
            File::put($databasePath, '');
        }

        if (!File::exists($databasePath)) {
            throw new \Exception('Unable to create SQLite file. Ensure /database directory is writable.');
        }

        // Normalize path for Windows (forward slashes)
        $normalizedPath = str_replace('\\', '/', $databasePath);

        // Do NOT rewrite .env during installer to prevent server restart loop
        // We configure DB at runtime instead

        return $databasePath;
    }

    private function updateEnv($data)
    {
        $envPath = base_path('.env');
        $env = file_get_contents($envPath);
        $originalEnv = $env;

        foreach ($data as $key => $value) {
            $value = str_replace('\\', '/', $value);
            $newLine = "{$key}=\"{$value}\"";

            if (preg_match("/^{$key}=.*/m", $env)) {
                $env = preg_replace("/^{$key}=.*/m", $newLine, $env);
            } else {
                $env .= "\n{$newLine}";
            }
        }

        // Prevent infinite artisan serve restart loop
        if ($env !== $originalEnv) {
            file_put_contents($envPath, $env);
        }
    }

    public function render()
    {
        return view('installer.installer');
    }
}
