<?php

namespace App\Providers;

use App\Contracts\EsigefGatewayInterface;
use App\Contracts\SignatureProviderInterface;
use App\Integrations\Esigef\CsvEsigefGateway;
use App\Integrations\Signature\ExternalSignatureProvider;
use App\Integrations\Signature\MockSignatureProvider;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use PDO;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SignatureProviderInterface::class, fn () => app()->environment('testing') ? app(MockSignatureProvider::class) : app(ExternalSignatureProvider::class));
        $this->app->bind(EsigefGatewayInterface::class, CsvEsigefGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DB::extend('odbc', function (array $config, string $name): Connection {
            $pdo = new PDO(
                $config['dsn'],
                $config['username'] ?? null,
                $config['password'] ?? null,
                $config['options'] ?? []
            );

            return new Connection(
                $pdo,
                $config['database'] ?? '',
                $config['prefix'] ?? '',
                $config
            );
        });

        Gate::before(fn (User $user, string $ability): ?bool => $user->hasPermission($ability) ? true : null);

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by($request->ip().'|'.$request->string('email')->lower()));
    }
}
