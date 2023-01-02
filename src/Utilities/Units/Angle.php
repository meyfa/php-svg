<?php

namespace SVG\Utilities\Units;

use SVG\Shims\Str;

final class Angle
{
    /**
     * Converts an angle (specified with deg, rad, grad, turn, or no unit) into
     * the corresponding number of degrees. Numbers without a unit default to
     * degrees. The result is NOT clamped.
     *
     * @param string|null $input The SVG angle string to convert.
     *
     * @return float|null The angle in degrees the given string denotes, or null on parse error.
     */
    public static function convert(?string $input): ?float
    {
        $normalizedInput = Str::trim($input);

        $regex = '/^([+-]?\d*\.?\d*)(deg|rad|grad|turn)?$/';
        if (!preg_match($regex, $normalizedInput, $matches) || $matches[1] === '') {
            return null;
        }

        $factors = [
            'deg'  => (1),          // base unit
            'rad'  => (180 / M_PI), // 1rad = (180/pi)deg
            'grad' => (9 / 10),     // 10grad = 9deg
            'turn' => (360),        // 1turn = 360deg
        ];

        $value = (float) $matches[1];
        $unit  = empty($matches[2]) ? 'deg' : $matches[2];

        return $value * $factors[$unit];
    }
}
