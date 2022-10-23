<?php

namespace SVG\Shims;

class Str
{
    /**
     * Strip whitespace (or other characters) from the beginning and end of a string.
     *
     * This is a shim for PHP's native trim() function.
     * As calling trim() on null is deprecated as of PHP 8.1 this method reproduces the behaviour of <8.1.
     *
     * @param string $string
     * @param string $characters
     *
     * @return string
     */
    public static function trim($string, $characters = " \n\r\t\v\x00")
    {
        if ($string === null) {
            $string = (string)$string;
        }

        return trim($string, $characters);
    }
}
