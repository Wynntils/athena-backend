<?php

namespace App\Services;

use Br33f\Ga4\MeasurementProtocol\Dto\Event\BaseEvent;
use Br33f\Ga4\MeasurementProtocol\Dto\Request\BaseRequest;

class AnalyticsService
{
    public function buildCapeSubmittedRequest(string $username): BaseRequest
    {
        $baseRequest = new BaseRequest;
        $baseRequest->setClientId(md5($username));

        $capeEvent = new BaseEvent('cape_submitted');
        $capeEvent->setParamValue('username', $username);
        $capeEvent->setParamValue('engagement_time_msec', '1');

        $baseRequest->addEvent($capeEvent);

        return $baseRequest;
    }
}
