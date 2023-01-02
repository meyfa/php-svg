<?php

namespace SVG\Utilities\Units;

use SVG\Shims\Str;

final class Length
{
    /**
     * Converts any valid SVG length string into an absolute pixel length,
     * using the given canvas width as reference for percentages.
     *
     * If the input is null or is a string that does not denote a valid length unit, null is returned.
     *
     * @param string|null $input      The SVG length string to convert.
     * @param float|null  $viewLength The canvas width to use as reference length (if available).
     *
     * @return float|null The absolute pixel number the given string denotes, or null on parse error.
     */
    public static function convert(?string $input, ?float $viewLength): ?float
    {
        $normalizedInput = Str::trim($input);

        $regex = '/^([+-]?\d*\.?\d*)(px|pt|pc|cm|mm|in|%)?$/';
        if (!preg_match($regex, $normalizedInput, $matches) || $matches[1] === '') {
            return null;
        }

        $factors = [
            'px' => (1),                    // base unit
            'pt' => (16 / 12),              // 12pt = 16px
            'pc' => (16),                   // 1pc = 16px
            'in' => (96),                   // 1in = 96px
            'cm' => (96 / 2.54),            // 1in = 96px, 1in = 2.54cm
            'mm' => (96 / 25.4),            // 1in = 96px, 1in = 25.4mm
            '%'  => (($viewLength ?? 0) / 100),    // 1% = 1/100 of viewLength
        ];

        $value = (float) $matches[1];
        $unit  = empty($matches[2]) ? 'px' : $matches[2];

        return $value * $factors[$unit];
    }
}
