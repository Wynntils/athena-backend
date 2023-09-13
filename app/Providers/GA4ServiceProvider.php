<?php

namespace App\Providers;

use Br33f\Ga4\MeasurementProtocol\Service;
use Illuminate\Support\ServiceProvider;

class GA4ServiceProvider extends ServiceProvider
{
    public function register(): void
    {

        $this->app->singleton(Service::class, function () {
            // Create service instance
            $ga4Service = new Service(config('ga4.secret'));
            $ga4Service->setMeasurementId(config('ga4.measurement_id'));

            return $ga4Service;
        });
    }
}
