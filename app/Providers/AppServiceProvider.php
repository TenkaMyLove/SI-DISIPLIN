<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auto-initialize SQLite database file and tables if missing
        if (config('database.default') === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');
            if ($dbPath && $dbPath !== ':memory:' && ! file_exists($dbPath)) {
                $dir = dirname($dbPath);
                if (! is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                touch($dbPath);

                try {
                    \Illuminate\Support\Facades\Artisan::call('migrate', [
                        '--force' => true,
                        '--seed' => true,
                    ]);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Auto database initialization failed: ' . $e->getMessage());
                }
            }
        }
    }
}
