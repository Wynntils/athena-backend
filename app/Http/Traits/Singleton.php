<?php

namespace App\Http\Traits;

trait Singleton
{

    static private ?self $instance = null;

    /**
     * To return new or existing Singleton instance of the class from which it is called.
     * As it sets to final it can't be overridden.
     *
     * @return self Singleton instance of the class.
     */
    final public static function instance(): static
    {
        if (!self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

}
