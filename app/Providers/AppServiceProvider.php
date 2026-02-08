<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Yajra\DataTables\Html\Builder;

use App\Repositories\Contracts\UserTrackingRepositoryInterface;
use App\Repositories\Contracts\DeviceRepositoryInterface;
use App\Repositories\Contracts\AcquisitionAttributionInterface;
use App\Repositories\Contracts\BreathSessionRepositoryInterface;

use App\Repositories\Eloquent\UserTrackingRepository;
use App\Repositories\Eloquent\DeviceRepository;
use App\Repositories\Eloquent\AcquisitionAttributionRepository;
use App\Repositories\Eloquent\BreathSessionRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserTrackingRepositoryInterface::class,
            UserTrackingRepository::class
        );

        $this->app->bind(
            DeviceRepositoryInterface::class,
            DeviceRepository::class
        );

        $this->app->bind(
            AcquisitionAttributionInterface::class,
            AcquisitionAttributionRepository::class
        );

        $this->app->bind(
            BreathSessionRepositoryInterface::class,
            BreathSessionRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Builder::useVite();
    }
}
