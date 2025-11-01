<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class InstallController extends Controller
{
    /**
     * Run first-time installation steps. Idempotent: runs only if not installed.
     */
    public function install(): JsonResponse
    {
        $flagPath = 'app/installed';

        if (Storage::exists($flagPath)) {
            return response()->json([
                'installed' => true,
                'message' => 'Application is already installed.'
            ], 409);
        }

        $steps = [];

        // Run migrations
        try {
            $exit = Artisan::call('migrate', ['--force' => true]);
            $steps['migrate'] = [
                'exit_code' => $exit,
                'output' => Artisan::output(),
            ];
        } catch (\Throwable $e) {
            return response()->json([
                'installed' => false,
                'step' => 'migrate',
                'error' => $e->getMessage(),
            ], 500);
        }

        // Create storage symlink
        try {
            $exit = Artisan::call('storage:link');
            $steps['storage_link'] = [
                'exit_code' => $exit,
                'output' => Artisan::output(),
            ];
        } catch (\Throwable $e) {
            // Not fatal; continue
            $steps['storage_link'] = [
                'exit_code' => 1,
                'error' => $e->getMessage(),
            ];
        }

        // Seed database (optional: can be skipped if not desired)
        try {
            $exit = Artisan::call('db:seed', ['--force' => true]);
            $steps['seed'] = [
                'exit_code' => $exit,
                'output' => Artisan::output(),
            ];
        } catch (\Throwable $e) {
            // Not fatal; continue
            $steps['seed'] = [
                'exit_code' => 1,
                'error' => $e->getMessage(),
            ];
        }

        // Write installed flag
        Storage::put($flagPath, now()->toDateTimeString());

        return response()->json([
            'installed' => true,
            'message' => 'Installation completed successfully.',
            'steps' => $steps,
        ]);
    }

    /**
     * Return installation status
     */
    public function status(): JsonResponse
    {
        $flagPath = 'app/installed';
        $installed = Storage::exists($flagPath);
        return response()->json([
            'installed' => $installed,
            'timestamp' => $installed ? Storage::get($flagPath) : null,
        ]);
    }

    /**
     * Configure basic app settings (APP_NAME, DB_DATABASE) before install.
     */
    public function configure(Request $request): JsonResponse
    {
        $data = $request->validate([
            'app_name' => 'required|string|max:100',
            'db_database' => 'required|string|max:100',
            'admin_name' => 'sometimes|required|string|max:255',
            'admin_email' => 'sometimes|required|email',
            'admin_password' => 'sometimes|required|min:6|confirmed',
        ]);

        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            // Try to bootstrap from example file
            $example = base_path('.env.example');
            if (file_exists($example)) {
                try { copy($example, $envPath); } catch (\Throwable $e) {}
            }
        }
        if (!file_exists($envPath)) {
            return response()->json(['message' => '.env file not found and could not be created from .env.example', 'path' => $envPath], 500);
        }

        // Ensure file is writable
        if (!is_writable($envPath)) {
            @chmod($envPath, 0664);
        }
        if (!is_writable($envPath)) {
            return response()->json([
                'message' => 'Unable to write .env file (permission denied)',
                'path' => $envPath,
                'hint' => 'Run your editor/terminal as Administrator, or grant write permission to the web server user.'
            ], 500);
        }

        try {
            $env = file_get_contents($envPath);
            if ($env === false) {
                return response()->json(['message' => 'Unable to read .env file'], 500);
            }
            $env = $this->setEnvValue($env, 'APP_NAME', '"' . addslashes($data['app_name']) . '"');
            $env = $this->setEnvValue($env, 'DB_DATABASE', $data['db_database']);

            $written = file_put_contents($envPath, $env);
            if ($written === false) {
                return response()->json(['message' => 'Unable to write .env file (permission denied)'], 500);
            }
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to save config', 'error' => $e->getMessage()], 500);
        }

        // Clear config cache so new .env values take effect
        try { Artisan::call('config:clear'); } catch (\Throwable $e) {}
        try { Artisan::call('cache:clear'); } catch (\Throwable $e) {}

        $admin = null;
        if (!empty($data['admin_email'])) {
            $admin = User::where('email', $data['admin_email'])->first();
            if (!$admin) {
                $admin = User::create([
                    'name' => $data['admin_name'] ?? 'Master User',
                    'email' => $data['admin_email'],
                    'password' => Hash::make($data['admin_password']),
                ]);
            } else if (!empty($data['admin_password'])) {
                $admin->update([
                    'name' => $data['admin_name'] ?? $admin->name,
                    'password' => Hash::make($data['admin_password'])
                ]);
            }
        }

        return response()->json([
            'message' => 'Configuration saved successfully',
            'app_name' => $data['app_name'],
            'db_database' => $data['db_database'],
            'admin_user' => $admin ? [ 'id' => $admin->id, 'email' => $admin->email ] : null,
        ]);
    }

    private function setEnvValue(string $env, string $key, string $value): string
    {
        $pattern = "/^{$key}=.*$/m";
        $line = $key . '=' . $value;
        if (preg_match($pattern, $env)) {
            return preg_replace($pattern, $line, $env);
        }
        return rtrim($env) . PHP_EOL . $line . PHP_EOL;
    }
}
