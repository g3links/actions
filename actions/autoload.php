<?php

spl_autoload_register(function ($class) {

    if (\is_file($file = dirname(__FILE__) . '/' . str_replace(['\\', "\0"], ['/', ''], $class) . '.php')) {
        require $file;
    }
});
