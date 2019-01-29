<?php

namespace model;

class lexi {

    private static $lexifilename;
    private static $lexi = [];
    const language = 'en';
    const country = 'US';
    const syspackage = "sysmssg";
    const pathlexifiles = '{0}theme/lexi/{1}/{2}.json';

    public static function getLang() {
        if (filter_input(INPUT_COOKIE,'lang') === null)
            return self::language;

        $parsedata = \explode('-', filter_input(INPUT_COOKIE,'lang'));
        if (isset($parsedata[0]))
            return $parsedata[0];

        return self::language;
    }
    
    public static function getLangCountry() {
        if (filter_input(INPUT_COOKIE,'lang') !== null)
            return filter_input(INPUT_COOKIE,'lang');

        return self::language . '-' . self::country;
    }
    
    public static function getall($package) {
        if (!isset($package)) 
            return [];

        if (empty($package)) 
            $package = self::syspackage;

        $filename = \model\utils::format(self::pathlexifiles, DIR_APP, $package, self::getLang());
        if (self::$lexifilename !== $filename) {
            self::$lexi = [];
            if (\is_file($filename)) {
                $jlexi = \file_get_contents($filename);
                self::$lexi = json_decode($jlexi, true);
            }
        }

        return self::$lexi;
    }

    public static function get() {

        $rawargs = func_get_args();
        if (!$rawargs) 
            return '';

        if (!isset($rawargs)) 
            return '';

        if (count($rawargs) < 2) 
            return '';

        $package = $rawargs[0];
        if (!isset($package)) 
            return '';

        unset($rawargs[0]);
        $key = $rawargs[1];
        if (!isset($key) || empty($key)) 
            return '';

        unset($rawargs[1]);

        $args = null;
        if (count($rawargs) > 0) 
            $args = array_values($rawargs);

        $lexi = self::getall($package);
        if (isset($lexi[$key])) {
            $val = $lexi[$key];
            if (!is_null($args))
                $val = call_user_func_array('\model\utils::format', array_merge([$lexi[$key]], $args));
        }
        return $val ?? $key;
    }

}
