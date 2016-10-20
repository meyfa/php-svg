<?php

namespace JangoBrick\SVG;

// class with helper methods

final class SVG
{
    // takes strings like 10px or 12.5% and returns length to render
    public static function convertUnit($unit, $viewLength)
    {
        $matches = array();
        $match   = preg_match('/^([+-]?\\d*\\.?\\d*)(px|%)?$/', $unit, $matches);

        if (!$match) {
            return false;
        }

        $num  = floatval($matches[1]);
        $unit = $matches[2];

        if ($unit === 'px' || $unit === null) {
            return $num;
        } elseif ($unit === '%') {
            return ($num / 100) * $viewLength;
        }

        return;
    }

    // regex for #FFFFFF etc
    const COLOR_HEX_6 = '/^#([0-9A-F]{2})([0-9A-F]{2})([0-9A-F]{2})$/i';
    // regex for #FFF etc
    const COLOR_HEX_3 = '/^#([0-9A-F])([0-9A-F])([0-9A-F])$/i';

    // regex for rgb(255, 255, 255) etc
    const COLOR_RGB = '/^rgb\\(([+-]?\\d*\\.?\\d*)\\s*,\\s*([+-]?\\d*\\.?\\d*)\\s*,\\s*([+-]?\\d*\\.?\\d*)\\)$/';
    // regex for rgba(255, 255, 255, 0.5) etc
    const COLOR_RGBA = '/^rgba\\(([+-]?\\d*\\.?\\d*)\\s*,\\s*([+-]?\\d*\\.?\\d*)\\s*,\\s*([+-]?\\d*\\.?\\d*)\\s*,\\s*([+-]?\\d*\\.?\\d*)\\)$/';

    /**
     * Converts any valid SVG color string into an array of RGBA components.
     *
     * All of the components are ints 0-255.
     *
     * @param string $color The color string to convert, as specified in SVG.
     *
     * @return int[] The color converted to RGBA components.
     */
    public static function parseColor($color)
    {
        $matches = array();

        $r = 0;
        $g = 0;
        $b = 0;
        $a = 255;

        if (preg_match(self::COLOR_HEX_6, $color, $matches)) {
            $r = hexdec($matches[1]);
            $g = hexdec($matches[2]);
            $b = hexdec($matches[3]);
        } elseif (preg_match(self::COLOR_HEX_3, $color, $matches)) {
            $r = hexdec($matches[1].$matches[1]);
            $g = hexdec($matches[2].$matches[2]);
            $b = hexdec($matches[3].$matches[3]);
        } elseif (preg_match(self::COLOR_RGB, $color, $matches)) {
            $r = intval($matches[1]);
            $g = intval($matches[2]);
            $b = intval($matches[3]);
        } elseif (preg_match(self::COLOR_RGBA, $color, $matches)) {
            $r = intval($matches[1]);
            $g = intval($matches[2]);
            $b = intval($matches[3]);
            $a = intval(floatval($matches[4]) * 255);
        }

        $r = min(max($r, 0), 255);
        $g = min(max($g, 0), 255);
        $b = min(max($b, 0), 255);
        $a = min(max($a, 0), 255);

        return array($r, $g, $b, $a);
    }
}
