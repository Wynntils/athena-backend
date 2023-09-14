<?php

namespace App\Listeners;

use App\Events\LoginEvent;
use Br33f\Ga4\MeasurementProtocol\Dto\Common\UserProperties;
use Br33f\Ga4\MeasurementProtocol\Dto\Common\UserProperty;
use Br33f\Ga4\MeasurementProtocol\Dto\Event\LoginEvent as GALoginEvent;
use Br33f\Ga4\MeasurementProtocol\Dto\Request\BaseRequest;
use Br33f\Ga4\MeasurementProtocol\Service;

class LoginEventListener
{
    public function __construct(public Service $ga4Service)
    {
    }

    public function handle(LoginEvent $event): void
    {
        // Create base request
        $baseRequest = new BaseRequest();
        $baseRequest->setClientId($event->user->id);
        $baseRequest->setUserId($event->user->id);
        $baseRequest->setUserProperties(new UserProperties([
            new UserProperty('version', $event->user->latestVersion),
        ]));

        // Create event
        $loginEvent = new GALoginEvent();
        $loginEvent->setMethod($event->method);

        // Add event to base request
        $baseRequest->addEvent($loginEvent);

        // Send request
        $this->ga4Service->send($baseRequest);
    }
}
