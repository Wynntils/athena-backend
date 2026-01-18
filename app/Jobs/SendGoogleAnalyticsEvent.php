<?php

namespace App\Jobs;

use Br33f\Ga4\MeasurementProtocol\Dto\Request\BaseRequest;
use Br33f\Ga4\MeasurementProtocol\Exception\HydrationException;
use Br33f\Ga4\MeasurementProtocol\Exception\ValidationException;
use Br33f\Ga4\MeasurementProtocol\Service;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendGoogleAnalyticsEvent implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public BaseRequest $baseRequest
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(Service $ga4Service): void
    {
        try {
            $ga4Service->send($this->baseRequest);
        } catch (HydrationException|ValidationException $e) {
            // Log the error silently if needed
        }
    }
}
