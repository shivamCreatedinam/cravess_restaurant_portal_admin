<?php

namespace App\Providers;

use App\Interfaces\CommonServiceInterface;
use App\Interfaces\RestaurantAuthInterface;
use App\Interfaces\RestaurantCommonInterface;
use App\Interfaces\RestaurantKycInterface;
use App\Interfaces\UserInterface;
use App\Models\User;
use App\Repositories\CommonServiceRepository;
use App\Repositories\EloquentUserRepository;
use App\Repositories\RestaurantAuthRepository;
use App\Repositories\RestaurantCommonRepository;
use App\Repositories\RestaurantKycRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(UserInterface::class, function () {
            return new EloquentUserRepository(new User());
        });
        $this->app->singleton(CommonServiceInterface::class, CommonServiceRepository::class);
        $this->app->singleton(RestaurantAuthInterface::class, RestaurantAuthRepository::class);
        $this->app->singleton(RestaurantKycInterface::class, RestaurantKycRepository::class);
        $this->app->singleton(RestaurantCommonInterface::class, RestaurantCommonRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
