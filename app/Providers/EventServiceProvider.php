<?php

namespace App\Providers;

use App\Events\LoginEvent;
use App\Events\SignUpEvent;
use App\Listeners\ConnectionFailedListener;
use App\Listeners\LoginEventListener;
use App\Listeners\SignUpEventListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Http\Client\Events\ConnectionFailed;
use SocialiteProviders\Discord\DiscordExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Minecraft\MinecraftExtendSocialite;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ConnectionFailed::class => [
            ConnectionFailedListener::class,
        ],
        SocialiteWasCalled::class => [
            // add your listeners (aka providers) here
            MinecraftExtendSocialite::class,
            DiscordExtendSocialite::class,
        ],
        LoginEvent::class => [
            LoginEventListener::class,
        ],
        SignUpEvent::class => [
            SignUpEventListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
