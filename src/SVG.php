<?php

namespace JangoBrick\SVG;

/**
 * This class contains general helper methods for understanding SVG files.
 */
final class SVG
{
    /** @var string COLOR_HEX_6 A RegEx for #FFFFFF etc */
    const COLOR_HEX_6 = '/^#([0-9A-F]{2})([0-9A-F]{2})([0-9A-F]{2})$/i';
    /** @var string COLOR_HEX_3 A RegEx for #FFF etc */
    const COLOR_HEX_3 = '/^#([0-9A-F])([0-9A-F])([0-9A-F])$/i';

    /** @var string COLOR_RGB A RegEx for rgb(255, 255, 255) etc (with percentage support) */
    const COLOR_RGB = '/^rgb\(([+-]?\d*\.?\d*%?)\s*,\s*([+-]?\d*\.?\d*%?)\s*,\s*([+-]?\d*\.?\d*%?)\)$/';
    /** @var string COLOR_RGBA A RegEx for rgba(255, 255, 255, 0.5) etc (with percentage support) */
    const COLOR_RGBA = '/^rgba\(([+-]?\d*\.?\d*%?)\s*,\s*([+-]?\d*\.?\d*%?)\s*,\s*([+-]?\d*\.?\d*%?)\s*,\s*([+-]?\d*\.?\d*)\)$/';

    /** @var string COLOR_HSL A RegEx for hsl(240, 100%, 100%) etc */
    const COLOR_HSL = '/^hsl\(([+-]?\d*\.?\d*)\s*,\s*([+-]?\d*\.?\d*%)\s*,\s*([+-]?\d*\.?\d*%)\)$/';
    /** @var string COLOR_HSLA A RegEx for hsla(240, 100%, 100%, 0.5) etc */
    const COLOR_HSLA = '/^hsla\(([+-]?\d*\.?\d*)\s*,\s*([+-]?\d*\.?\d*%)\s*,\s*([+-]?\d*\.?\d*%)\s*,\s*([+-]?\d*\.?\d*)\)$/';

    /**
     * Converts any valid SVG length string into an absolute pixel length,
     * using the given canvas width.
     *
     * @param string $unit       The SVG length string to convert.
     * @param int    $viewLength The canvas width to use as reference length.
     *
     * @return float The absolute length in pixels the given string denotes.
     */
    public static function convertUnit($unit, $viewLength)
    {
        $matches = array();
        $match   = preg_match('/^([+-]?\d*\.?\d*)(px|pt|pc|cm|mm|in|%)?$/', $unit, $matches);

        if (!$match) {
            return false;
        }

        $num  = floatval($matches[1]);
        $unit = isset($matches[2]) ? $matches[2] : null;

        switch ($unit) {
            case 'pt':
                // 12pt == 16px, so ratio = 12/16 = 0.75
                return $num / 0.75;
            case 'pc':
                // 1pc == 12pt == 16px
                return $num * 16;
            case 'cm':
                // 2.54cm == 1in == 96px, so ratio = 2.54/96
                return $num / (2.54 / 96);
            case 'mm':
                // 25.4mm == 1in == 96px, so ratio = 25.4/96
                return $num / (25.4 / 96);
            case 'in':
                // 1in == 96px
                return $num * 96;
            case '%':
                return ($num / 100) * $viewLength;
            case 'px':
            default:
                return $num;
        }
    }

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
        $colorLower = strtolower($color);
        if (isset(self::$namedColors[$colorLower])) {
            return self::$namedColors[$colorLower];
        }

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
            $r = self::parseRGBComponent($matches[1]);
            $g = self::parseRGBComponent($matches[2]);
            $b = self::parseRGBComponent($matches[3]);
        } elseif (preg_match(self::COLOR_RGBA, $color, $matches)) {
            $r = self::parseRGBComponent($matches[1]);
            $g = self::parseRGBComponent($matches[2]);
            $b = self::parseRGBComponent($matches[3]);
            $a = intval(floatval($matches[4]) * 255);
        } elseif (preg_match(self::COLOR_HSL, $color, $matches)) {
            $h = floatval($matches[1]);
            $s = floatval($matches[2]) / 100;
            $l = floatval($matches[3]) / 100;

            list($r, $g, $b) = self::convertHSLtoRGB($h, $s, $l);
        } elseif (preg_match(self::COLOR_HSLA, $color, $matches)) {
            $h = floatval($matches[1]);
            $s = floatval($matches[2]) / 100;
            $l = floatval($matches[3]) / 100;
            $a = intval(floatval($matches[4]) * 255);

            list($r, $g, $b) = self::convertHSLtoRGB($h, $s, $l);
        }

        $r = min(max($r, 0), 255);
        $g = min(max($g, 0), 255);
        $b = min(max($b, 0), 255);
        $a = min(max($a, 0), 255);

        return array($r, $g, $b, $a);
    }

    /**
     * Converts the provided component string (either percentage or number)
     * into a color component int (0 - 255).
     *
     * @param string $component The component string.
     *
     * @return int The parsed component int (0 - 255).
     */
    private static function parseRGBComponent($component)
    {
        $matches = array();
        if (preg_match('/^([+-]?\d*\.?\d*)%$/', $component, $matches)) {
            return intval(floatval($matches[1]) * (255 / 100));
        }

        return intval($component);
    }

    /**
     * Takes three arguments H (0 - 360), S (0 - 1), L (0 - 1) and converts them
     * to RGB components (0 - 255).
     *
     * @param float $h The hue.
     * @param float $s The saturation.
     * @param float $l The lightness.
     *
     * @return int[] An RGB array with values ranging from 0 - 255 each.
     */
    private static function convertHSLtoRGB($h, $s, $l)
    {
        $h = fmod($h, 360);
        if ($h < 0) {
            $h += 360;
        }
        $s = min(max($s, 0), 1);
        $l = min(max($l, 0), 1);

        if ($s == 0) {
            // shortcut if grayscale
            return array(intval($l * 255), intval($l * 255), intval($l * 255));
        }

        // compute intermediates
        $m2 = ($l <= 0.5) ? ($l * (1 + $s)) : ($l + $s - $l * $s);
        $m1 = 2 * $l - $m2;

        // convert intermediates + hue to components
        $r = self::convertHSLHueToRGBComponent($m1, $m2, $h + 120);
        $g = self::convertHSLHueToRGBComponent($m1, $m2, $h);
        $b = self::convertHSLHueToRGBComponent($m1, $m2, $h - 120);

        return array($r, $g, $b);
    }

    /**
     * Takes the two intermediate values from `convertHSLtoRGB()` and the hue,
     * and computes the component's value.
     *
     * @param float $m1  Intermediate 1.
     * @param float $m2  Intermediate 2.
     * @param float $hue The hue, adapted to the component (0 - 360).
     *
     * @return int The component's value (0 - 255).
     */
    private static function convertHSLHueToRGBComponent($m1, $m2, $hue)
    {
        if ($hue < 0) {
            $hue += 360;
        } elseif ($hue > 360) {
            $hue -= 360;
        }

        $v = $m1;

        if ($hue < 60) {
            $v = $m1 + ($m2 - $m1) * $hue / 60;
        } elseif ($hue < 180) {
            $v = $m2;
        } elseif ($hue < 240) {
            $v = $m1 + ($m2 - $m1) * (240 - $hue) / 60;
        }

        return intval($v * 255);
    }

    /**
     * @var array[] $namedColors A map of color names to their RGBA arrays.
     * @see https://www.w3.org/TR/SVG11/types.html#ColorKeywords For the source.
     */
    private static $namedColors = array(
        'transparent'           => array(  0,   0,   0,   0),
        'aliceblue'             => array(240, 248, 255, 255),
        'antiquewhite'          => array(250, 235, 215, 255),
        'aqua'                  => array(  0, 255, 255, 255),
        'aquamarine'            => array(127, 255, 212, 255),
        'azure'                 => array(240, 255, 255, 255),
        'beige'                 => array(245, 245, 220, 255),
        'bisque'                => array(255, 228, 196, 255),
        'black'                 => array(  0,   0,   0, 255),
        'blanchedalmond'        => array(255, 235, 205, 255),
        'blue'                  => array(  0,   0, 255, 255),
        'blueviolet'            => array(138,  43, 226, 255),
        'brown'                 => array(165,  42,  42, 255),
        'burlywood'             => array(222, 184, 135, 255),
        'cadetblue'             => array( 95, 158, 160, 255),
        'chartreuse'            => array(127, 255,   0, 255),
        'chocolate'             => array(210, 105,  30, 255),
        'coral'                 => array(255, 127,  80, 255),
        'cornflowerblue'        => array(100, 149, 237, 255),
        'cornsilk'              => array(255, 248, 220, 255),
        'crimson'               => array(220,  20,  60, 255),
        'cyan'                  => array(  0, 255, 255, 255),
        'darkblue'              => array(  0,   0, 139, 255),
        'darkcyan'              => array(  0, 139, 139, 255),
        'darkgoldenrod'         => array(184, 134,  11, 255),
        'darkgray'              => array(169, 169, 169, 255),
        'darkgreen'             => array(  0, 100,   0, 255),
        'darkgrey'              => array(169, 169, 169, 255),
        'darkkhaki'             => array(189, 183, 107, 255),
        'darkmagenta'           => array(139,   0, 139, 255),
        'darkolivegreen'        => array( 85, 107,  47, 255),
        'darkorange'            => array(255, 140,   0, 255),
        'darkorchid'            => array(153,  50, 204, 255),
        'darkred'               => array(139,   0,   0, 255),
        'darksalmon'            => array(233, 150, 122, 255),
        'darkseagreen'          => array(143, 188, 143, 255),
        'darkslateblue'         => array( 72,  61, 139, 255),
        'darkslategray'         => array( 47,  79,  79, 255),
        'darkslategrey'         => array( 47,  79,  79, 255),
        'darkturquoise'         => array(  0, 206, 209, 255),
        'darkviolet'            => array(148,   0, 211, 255),
        'deeppink'              => array(255,  20, 147, 255),
        'deepskyblue'           => array(  0, 191, 255, 255),
        'dimgray'               => array(105, 105, 105, 255),
        'dimgrey'               => array(105, 105, 105, 255),
        'dodgerblue'            => array( 30, 144, 255, 255),
        'firebrick'             => array(178,  34,  34, 255),
        'floralwhite'           => array(255, 250, 240, 255),
        'forestgreen'           => array( 34, 139,  34, 255),
        'fuchsia'               => array(255,   0, 255, 255),
        'gainsboro'             => array(220, 220, 220, 255),
        'ghostwhite'            => array(248, 248, 255, 255),
        'gold'                  => array(255, 215,   0, 255),
        'goldenrod'             => array(218, 165,  32, 255),
        'gray'                  => array(128, 128, 128, 255),
        'grey'                  => array(128, 128, 128, 255),
        'green'                 => array(  0, 128,   0, 255),
        'greenyellow'           => array(173, 255,  47, 255),
        'honeydew'              => array(240, 255, 240, 255),
        'hotpink'               => array(255, 105, 180, 255),
        'indianred'             => array(205,  92,  92, 255),
        'indigo'                => array( 75,   0, 130, 255),
        'ivory'                 => array(255, 255, 240, 255),
        'khaki'                 => array(240, 230, 140, 255),
        'lavender'              => array(230, 230, 250, 255),
        'lavenderblush'         => array(255, 240, 245, 255),
        'lawngreen'             => array(124, 252,   0, 255),
        'lemonchiffon'          => array(255, 250, 205, 255),
        'lightblue'             => array(173, 216, 230, 255),
        'lightcoral'            => array(240, 128, 128, 255),
        'lightcyan'             => array(224, 255, 255, 255),
        'lightgoldenrodyellow'  => array(250, 250, 210, 255),
        'lightgray'             => array(211, 211, 211, 255),
        'lightgreen'            => array(144, 238, 144, 255),
        'lightgrey'             => array(211, 211, 211, 255),
        'lightpink'             => array(255, 182, 193, 255),
        'lightsalmon'           => array(255, 160, 122, 255),
        'lightseagreen'         => array( 32, 178, 170, 255),
        'lightskyblue'          => array(135, 206, 250, 255),
        'lightslategray'        => array(119, 136, 153, 255),
        'lightslategrey'        => array(119, 136, 153, 255),
        'lightsteelblue'        => array(176, 196, 222, 255),
        'lightyellow'           => array(255, 255, 224, 255),
        'lime'                  => array(  0, 255,   0, 255),
        'limegreen'             => array( 50, 205,  50, 255),
        'linen'                 => array(250, 240, 230, 255),
        'magenta'               => array(255,   0, 255, 255),
        'maroon'                => array(128,   0,   0, 255),
        'mediumaquamarine'      => array(102, 205, 170, 255),
        'mediumblue'            => array(  0,   0, 205, 255),
        'mediumorchid'          => array(186,  85, 211, 255),
        'mediumpurple'          => array(147, 112, 219, 255),
        'mediumseagreen'        => array( 60, 179, 113, 255),
        'mediumslateblue'       => array(123, 104, 238, 255),
        'mediumspringgreen'     => array(  0, 250, 154, 255),
        'mediumturquoise'       => array( 72, 209, 204, 255),
        'mediumvioletred'       => array(199,  21, 133, 255),
        'midnightblue'          => array( 25,  25, 112, 255),
        'mintcream'             => array(245, 255, 250, 255),
        'mistyrose'             => array(255, 228, 225, 255),
        'moccasin'              => array(255, 228, 181, 255),
        'navajowhite'           => array(255, 222, 173, 255),
        'navy'                  => array(  0,   0, 128, 255),
        'oldlace'               => array(253, 245, 230, 255),
        'olive'                 => array(128, 128,   0, 255),
        'olivedrab'             => array(107, 142,  35, 255),
        'orange'                => array(255, 165,   0, 255),
        'orangered'             => array(255,  69,   0, 255),
        'orchid'                => array(218, 112, 214, 255),
        'palegoldenrod'         => array(238, 232, 170, 255),
        'palegreen'             => array(152, 251, 152, 255),
        'paleturquoise'         => array(175, 238, 238, 255),
        'palevioletred'         => array(219, 112, 147, 255),
        'papayawhip'            => array(255, 239, 213, 255),
        'peachpuff'             => array(255, 218, 185, 255),
        'peru'                  => array(205, 133,  63, 255),
        'pink'                  => array(255, 192, 203, 255),
        'plum'                  => array(221, 160, 221, 255),
        'powderblue'            => array(176, 224, 230, 255),
        'purple'                => array(128,   0, 128, 255),
        'red'                   => array(255,   0,   0, 255),
        'rosybrown'             => array(188, 143, 143, 255),
        'royalblue'             => array( 65, 105, 225, 255),
        'saddlebrown'           => array(139,  69,  19, 255),
        'salmon'                => array(250, 128, 114, 255),
        'sandybrown'            => array(244, 164,  96, 255),
        'seagreen'              => array( 46, 139,  87, 255),
        'seashell'              => array(255, 245, 238, 255),
        'sienna'                => array(160,  82,  45, 255),
        'silver'                => array(192, 192, 192, 255),
        'skyblue'               => array(135, 206, 235, 255),
        'slateblue'             => array(106,  90, 205, 255),
        'slategray'             => array(112, 128, 144, 255),
        'slategrey'             => array(112, 128, 144, 255),
        'snow'                  => array(255, 250, 250, 255),
        'springgreen'           => array(  0, 255, 127, 255),
        'steelblue'             => array( 70, 130, 180, 255),
        'tan'                   => array(210, 180, 140, 255),
        'teal'                  => array(  0, 128, 128, 255),
        'thistle'               => array(216, 191, 216, 255),
        'tomato'                => array(255,  99,  71, 255),
        'turquoise'             => array( 64, 224, 208, 255),
        'violet'                => array(238, 130, 238, 255),
        'wheat'                 => array(245, 222, 179, 255),
        'white'                 => array(255, 255, 255, 255),
        'whitesmoke'            => array(245, 245, 245, 255),
        'yellow'                => array(255, 255,   0, 255),
        'yellowgreen'           => array(154, 205,  50, 255),
    );
}
