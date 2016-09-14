<?php

namespace JangoBrick\SVG;

// class with helper methods

final class SVG
{
    // takes strings like 10px or 12.5% and returns length to render
    public static function convertUnit($unit, $viewLength)
    {
        $matches = [];
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
    private static $COLOR_HEX_6 = '/^#([0-9A-F]{2})([0-9A-F]{2})([0-9A-F]{2})$/i';
    // regex for #FFF etc
    private static $COLOR_HEX_3 = '/^#([0-9A-F])([0-9A-F])([0-9A-F])$/i';

    // regex for rgb(255, 255, 255) etc
    private static $COLOR_RGB = '/^rgb\\(([+-]?\\d*\\.?\\d*)\\s*,\\s*([+-]?\\d*\\.?\\d*)\\s*,\\s*([+-]?\\d*\\.?\\d*)\\)$/';
    // regex for rgba(255, 255, 255, 0.5) etc
    private static $COLOR_RGBA = '/^rgba\\(([+-]?\\d*\\.?\\d*)\\s*,\\s*([+-]?\\d*\\.?\\d*)\\s*,\\s*([+-]?\\d*\\.?\\d*)\\s*,\\s*([+-]?\\d*\\.?\\d*)\\)$/';

    // takes any form of SVG color string and returns, depending on the second argument:
    // - FALSE (default): RGBA array
    //   R: 0-255; G: 0-255; B: 0-255; A: 0-127 (0 -> opaque, 127 = transparent)
    // - TRUE: ARGB integer that can directly be drawn with GD
    // The alpha range is halved and inverted because of the GD library, which
    // expects it to be like that.
    public static function parseColor($color, $argb_int = false)
    {
        $matches = [];

        $r = 0;
        $g = 0;
        $b = 0;
        $a = 0;

        if (preg_match(self::$COLOR_HEX_6, $color, $matches)) {
            $r = hexdec($matches[1]);
            $g = hexdec($matches[2]);
            $b = hexdec($matches[3]);
        } elseif (preg_match(self::$COLOR_HEX_3, $color, $matches)) {
            $r = hexdec($matches[1].$matches[1]);
            $g = hexdec($matches[2].$matches[2]);
            $b = hexdec($matches[3].$matches[3]);
        } elseif (preg_match(self::$COLOR_RGB, $color, $matches)) {
            $r = intval($matches[1]);
            $g = intval($matches[2]);
            $b = intval($matches[3]);
        } elseif (preg_match(self::$COLOR_RGBA, $color, $matches)) {
            $r = intval($matches[1]);
            $g = intval($matches[2]);
            $b = intval($matches[3]);
            $a = 127 - intval(floatval($matches[4]) * 127);
        }

        $r = min(max($r, 0), 255);
        $g = min(max($g, 0), 255);
        $b = min(max($b, 0), 255);
        $a = min(max($a, 0), 127);

        if ($argb_int) {
            return ($a << 24) + ($r << 16) + ($g << 8) + ($b);
        }

        return [$r, $g, $b, $a];
    }
}
