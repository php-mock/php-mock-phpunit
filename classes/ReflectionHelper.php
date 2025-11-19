<?php

namespace phpmock\phpunit;

/**
 * @internal
 */
final class ReflectionHelper
{
    private function __construct()
    {
    }

    public static function setAccessible($reflection)
    {
        // As of PHP 8.1.0, reflection setAccessible is no-op; all properties are accessible by default.
        if (PHP_VERSION_ID >= 80100) {
            return;
        }

        $reflection->setAccessible(true);
    }
}
