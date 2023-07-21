<?php

namespace ProductionPanic\BulkDelete\Common;

/**
 * This class implements the singleton pattern.
 *
 * This class is used to ensure that only one instance of a class is ever created.
 * This is helpful for classes that are used to store global state, such as a database connection, or general controller classes.
 */
abstract class Singleton {    
    private static $instances = [];

    private function __construct() {
        $this->onInit();
    }   

    public static function get(): static {
        if (!isset(static::$instances[static::class])) {
            static::$instances[static::class] = new static();
        }

        return static::$instances[static::class];
    }
    
    protected function onInit(){}
}