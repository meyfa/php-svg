<?php

/*
 * This file provides functions from older PHP versions that are no longer present in newer versions.
 * This is needed because we are on PHPUnit 4.8 which was built long before some things were deprecated.
 *
 * Load this file before anything else is run, preferably through php.ini auto_prepend_file.
 */

if (!function_exists('each')) {
    function each(&$array)
    {
        if (!is_array($array) && !is_object($array)) {
            return null;
        }
        $key = key($array);
        if ($key === null) {
            return false;
        }
        $value = $array[$key];
        next($array);
        return array(
            0 => $key,
            'key' => $key,
            1 => $value,
            'value' => $value,
        );
    }
}
