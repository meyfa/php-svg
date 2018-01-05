<?php

namespace SVG;

/**
 * This class contains general helper methods for understanding SVG files.
 */
final class SVG
{
    /**
     * Converts any valid SVG length string into an absolute pixel length,
     * using the given canvas width as reference for percentages.
     *
     * If the string does not denote a valid length unit, null is returned.
     *
     * @param string $unit       The SVG length string to convert.
     * @param float  $viewLength The canvas width to use as reference length.
     *
     * @return float|null The absolute pixel number the given string denotes.
     */
    public static function convertUnit($unit, $viewLength)
    {
        $regex = '/^([+-]?\d*\.?\d*)(px|pt|pc|cm|mm|in|%)?$/';
        if (!preg_match($regex, $unit, $matches) || $matches[1] === '') {
            return null;
        }

        $factors = array(
            'px' => (1),                    // base unit
            'pt' => (16 / 12),              // 12pt = 16px
            'pc' => (16),                   // 1pc = 16px
            'in' => (96),                   // 1in = 96px
            'cm' => (96 / 2.54),            // 1in = 96px, 1in = 2.54cm
            'mm' => (96 / 25.4),            // 1in = 96px, 1in = 25.4mm
            '%'  => ($viewLength / 100),    // 1% = 1/100 of viewLength
        );

        $value = floatval($matches[1]);
        $unit  = empty($matches[2]) ? 'px' : $matches[2];

        return $value * $factors[$unit];
    }

    /**
     * Converts an angle (specified with deg, rad, grad, turn, or no unit) into
     * the corresponding number of degrees. Numbers without a unit default to
     * degrees. The result is NOT clamped.
     *
     * @param string $unit The SVG angle string to convert.
     *
     * @return float The angle in degrees the given string denotes.
     */
    public static function convertAngleUnit($unit)
    {
        $regex = '/^([+-]?\d*\.?\d*)(deg|rad|grad|turn)?$/';
        if (!preg_match($regex, $unit, $matches) || $matches[1] === '') {
            return null;
        }

        $factors = array(
            'deg'  => (1),          // base unit
            'rad'  => (180 / M_PI), // 1rad = (180/pi)deg
            'grad' => (9 / 10),     // 10grad = 9deg
            'turn' => (360),        // 1turn = 360deg
        );

        $value = floatval($matches[1]);
        $unit  = empty($matches[2]) ? 'deg' : $matches[2];

        return $value * $factors[$unit];
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

        // pass on to dedicated functions depending on notation
        if (preg_match('/^#([0-9A-F]+)$/i', $color, $matches)) {
            list($r, $g, $b, $a) = self::parseHexComponents($matches[1]);
        } elseif (preg_match('/^rgba?\((.*)\)$/', $color, $matches)) {
            list($r, $g, $b, $a) = self::parseRGBAComponents($matches[1]);
        } elseif (preg_match('/^hsla?\((.*)\)$/', $color, $matches)) {
            list($r, $g, $b, $a) = self::parseHSLAComponents($matches[1]);
        }

        // any illegal component invalidates all components
        if (!isset($r) || !isset($g) || !isset($b) || !isset($a)) {
            return array(0, 0, 0, 0);
        }

        // clamp into integer range 0 - 255
        $r = min(max(intval($r), 0), 255);
        $g = min(max(intval($g), 0), 255);
        $b = min(max(intval($b), 0), 255);
        $a = min(max(intval($a), 0), 255);

        return array($r, $g, $b, $a);
    }

    /**
     * Takes a hex string of length 3, 4, 6 or 8 and converts it into an array
     * of floating-point RGBA components.
     *
     * For strings of invalid length, all components will be null.
     *
     * @param string $str The hexadecimal color string to convert.
     *
     * @return float[] The RGBA components (0 - 255).
     */
    private static function parseHexComponents($str)
    {
        $len = strlen($str);

        $r = $g = $b = $a = null;

        if ($len === 6 || $len === 8) {
            $r = hexdec($str[0].$str[1]);
            $g = hexdec($str[2].$str[3]);
            $b = hexdec($str[4].$str[5]);
            $a = $len === 8 ? hexdec($str[6].$str[7]) : 255;
        } elseif ($len === 3 || $len == 4) {
            $r = hexdec($str[0].$str[0]);
            $g = hexdec($str[1].$str[1]);
            $b = hexdec($str[2].$str[2]);
            $a = $len === 4 ? hexdec($str[3].$str[3]) : 255;
        }

        return array($r, $g, $b, $a);
    }

    /**
     * Takes a parameter string from the rgba functional notation
     * (i.e., the 'x' inside 'rgb(x)') and converts it into an array of
     * floating-point RGBA components.
     *
     * If any of the components could not be deduced, that component will be
     * null. No other component will be influenced.
     *
     * @param string $str The parameter string to convert.
     *
     * @return float[] The RGBA components.
     */
    private static function parseRGBAComponents($str)
    {
        $params = preg_split('/(\s*[\/,]\s*)|(\s+)/', trim($str));
        if (count($params) !== 3 && count($params) !== 4) {
            return array(null, null, null, null);
        }

        $r = self::parseRGBAComponent($params[0]);
        $g = self::parseRGBAComponent($params[1]);
        $b = self::parseRGBAComponent($params[2]);
        $a = count($params) < 4 ? 255 : self::parseRGBAComponent($params[3], 1, 255);

        return array($r, $g, $b, $a);
    }

    /**
     * Converts a single numeric color component (e.g. '10.5' or '20%') into a
     * floating-point value.
     *
     * The optional base argument represents 100%. It should be set to 255 for
     * the RGB components and to 1 for the A component.
     *
     * The optional scalar argument is the multiplier applied to the result. It
     * should be set to 1 for the RGB components (since they are already in the
     * correct final range) and to 255 for the A component (since it would
     * otherwise be between 0 and 1).
     *
     * @param string $str    The component string.
     * @param int    $base   The base value for percentages.
     * @param int    $scalar A multiplier for the final value.
     *
     * @return float The floating-point converted component.
     */
    private static function parseRGBAComponent($str, $base = 255, $scalar = 1)
    {
        $regex = '/^([+-]?(?:\d+|\d*\.\d+))(%)?$/';
        if (!preg_match($regex, $str, $matches)) {
            return null;
        }
        if (isset($matches[2]) && $matches[2] === '%') {
            return floatval($matches[1]) * $base / 100 * $scalar;
        }
        return floatval($matches[1]) * $scalar;
    }

    /**
     * Takes a parameter string from the hsla functional notation
     * (i.e., the 'x' inside 'hsl(x)') and converts it into an array of
     * floating-point RGBA components.
     *
     * If any of the components could not be deduced, that component will be
     * null. No other component will be influenced.
     *
     * @param string $str The parameter string to convert.
     *
     * @return float[] The RGBA components.
     */
    private static function parseHSLAComponents($str)
    {
        // split on delimiters
        $params = preg_split('/(\s*[\/,]\s*)|(\s+)/', trim($str));
        if (count($params) !== 3 && count($params) !== 4) {
            return null;
        }

        // parse HSL
        $h = self::convertAngleUnit($params[0]);
        $s = self::parseRGBAComponent($params[1], 1);
        $l = self::parseRGBAComponent($params[2], 1);

        // convert HSL to RGB
        $r = $g = $b = null;
        if (isset($h) && isset($s) && isset($l)) {
            list($r, $g, $b) = self::convertHSLtoRGB($h, $s, $l);
        }
        // add alpha
        $a = count($params) < 4 ? 255 : self::parseRGBAComponent($params[3], 1, 255);

        return array($r, $g, $b, $a);
    }

    /**
     * Takes three arguments H (0 - 360), S (0 - 1), L (0 - 1) and converts them
     * to RGB components (0 - 255).
     *
     * @param float $h The hue.
     * @param float $s The saturation.
     * @param float $l The lightness.
     *
     * @return float[] An RGB array with values ranging from 0 - 255 each.
     */
    private static function convertHSLtoRGB($h, $s, $l)
    {
        $s = min(max($s, 0), 1);
        $l = min(max($l, 0), 1);

        if ($s == 0) {
            // shortcut if grayscale
            return array($l * 255, $l * 255, $l * 255);
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
     * @param float $hue The hue, adapted to the component.
     *
     * @return float The component's value (0 - 255).
     */
    private static function convertHSLHueToRGBComponent($m1, $m2, $hue)
    {
        // bring hue into range (fmod assures that 0 <= abs($hue) < 360, while
        // the next step assures that it's positive)
        $hue = fmod($hue, 360);
        if ($hue < 0) {
            $hue += 360;
        }

        $v = $m1;

        if ($hue < 60) {
            $v = $m1 + ($m2 - $m1) * $hue / 60;
        } elseif ($hue < 180) {
            $v = $m2;
        } elseif ($hue < 240) {
            $v = $m1 + ($m2 - $m1) * (240 - $hue) / 60;
        }

        return $v * 255;
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
