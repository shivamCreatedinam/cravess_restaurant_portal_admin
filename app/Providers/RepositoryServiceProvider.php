<?php

namespace App\Providers;

use App\Interfaces\CommonServiceInterface;
use App\Interfaces\ProductInterface;
use App\Interfaces\RestaurantAuthInterface;
use App\Interfaces\RestaurantCommonInterface;
use App\Models\Product;
use App\Repositories\CommonServiceRepository;
use App\Repositories\EloquentProductRepository;
use App\Repositories\RestaurantAuthRepository;
use App\Repositories\RestaurantCommonRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // $this->app->singleton(UserInterface::class, function () {
        //     return new EloquentUserRepository(new User());
        // });
        $this->app->singleton(CommonServiceInterface::class, CommonServiceRepository::class);
        $this->app->singleton(RestaurantAuthInterface::class, RestaurantAuthRepository::class);
        $this->app->singleton(RestaurantCommonInterface::class, RestaurantCommonRepository::class);

        $this->app->singleton(ProductInterface::class, function () {
            return new EloquentProductRepository(new Product());
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
