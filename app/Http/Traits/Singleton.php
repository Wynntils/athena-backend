<?php

namespace App\Http\Traits;

trait Singleton
{
    /** @var static|null */
    private static ?self $instance = null;

    /**
     * To return new or existing Singleton instance of the class from which it is called.
     * As it sets to final it can't be overridden.
     *
     * @return static Singleton instance of the class.
     */
    final public static function instance(): static
    {
        if (! self::$instance) {
            // @phpstan-ignore new.static
            self::$instance = new static;
        }

        return self::$instance;
    }
}
