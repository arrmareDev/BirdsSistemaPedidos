<?php

namespace App\Providers;

use App\Repositories\ClientRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Services\ClientService;
use App\Services\OrderService;
use App\Services\ProductService;
use Illuminate\Support\ServiceProvider;
use App\Models\Order;
use App\Observers\OrderObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->singleton(ProductRepository::class);
        $this->app->singleton(OrderRepository::class);
        $this->app->singleton(ClientRepository::class);

        // Services
        $this->app->singleton(ProductService::class);
        $this->app->singleton(OrderService::class);
        $this->app->singleton(ClientService::class);
    }

    public function boot(): void
    {
        Order::observe(OrderObserver::class);
    }
}
