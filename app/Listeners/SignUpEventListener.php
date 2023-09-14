<?php

namespace App\Listeners;

use App\Events\SignUpEvent;
use Br33f\Ga4\MeasurementProtocol\Dto\Common\UserProperties;
use Br33f\Ga4\MeasurementProtocol\Dto\Common\UserProperty;
use Br33f\Ga4\MeasurementProtocol\Dto\Event\SignUpEvent as GASignUpEvent;
use Br33f\Ga4\MeasurementProtocol\Dto\Request\BaseRequest;
use Br33f\Ga4\MeasurementProtocol\Service;

class SignUpEventListener
{
    public function __construct(public Service $ga4Service)
    {
    }

    public function handle(SignUpEvent $event): void
    {
        // Create base request
        $baseRequest = new BaseRequest();
        $baseRequest->setClientId($event->user->id);
        $baseRequest->setUserId($event->user->id);
        $baseRequest->setUserProperties(new UserProperties([
            new UserProperty('version', $event->user->latestVersion),
        ]));

        // Create event
        $signUpEvent = new GASignUpEvent();
        $signUpEvent->setMethod($event->method);

        // Add event to base request
        $baseRequest->addEvent($signUpEvent);

        // Send request
        $this->ga4Service->send($baseRequest);
    }
}
