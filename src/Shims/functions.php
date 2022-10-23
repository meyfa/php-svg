<?php

/*
 * This file provides functions from older PHP versions that are no longer present in newer versions.
 *
 */

if (!function_exists('each')) {
    /**
     * Return the current key and value pair from an array and advance the array cursor.
     * After each() has executed, the array cursor will be left on the next element of the array,
     * or past the last element if it hits the end of the array.
     * You have to use reset() if you want to traverse the array again using each.
     *
     * This is needed because we are on PHPUnit 4.8 which was built long before each was deprecated.
     *
     * @param $array
     *
     * @return array|false|null
     */
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
