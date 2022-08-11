<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use ReflectionClass;
use ReflectionMethod;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function invokePrivateFunction($instance, string $method, array $args = [])
    {
        $refmethod = new ReflectionMethod($instance, $method);
        $refmethod->setAccessible(true);
        return $refmethod->invoke($instance, ...$args);
    }
}
