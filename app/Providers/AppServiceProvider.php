<?php

namespace App\Providers;

// use Illuminate\Pagination\Paginator;

use App\Repositories\Contracts\AcquisitionAttributionInterface;
use App\Repositories\Contracts\DeviceRepositoryInterface;
use App\Repositories\Eloquent\DeviceRepository;
use App\Repositories\Contracts\UserTrackingRepositoryInterface;
use App\Repositories\Eloquent\AcquisitionAttributionRepository;
use App\Repositories\Eloquent\UserTrackingRepository;
use Illuminate\Support\ServiceProvider;
use Yajra\DataTables\Html\Builder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserTrackingRepositoryInterface::class, UserTrackingRepository::class);
        $this->app->bind(DeviceRepositoryInterface::class, DeviceRepository::class);
        $this->app->bind(AcquisitionAttributionInterface::class, AcquisitionAttributionRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Builder::useVite();
        // Paginator::useBootstrapFive();
    }
}
