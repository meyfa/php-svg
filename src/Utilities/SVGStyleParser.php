<?php

namespace SVG\Utilities;

use SVG\Shims\Str;

/**
 * This is a utility class used to parse CSS rules.
 */
abstract class SVGStyleParser
{
    /**
     * Parses a string of CSS declarations into an associative array.
     *
     * @param string $string The CSS declarations.
     *
     * @return string[] An associative array of all declarations.
     */
    public static function parseStyles(string $string): array
    {
        $styles = [];
        if (empty($string)) {
            return $styles;
        }

        $declarations = preg_split('/\s*;\s*/', $string);

        foreach ($declarations as $declaration) {
            $declaration = Str::trim($declaration);
            if ($declaration === '') {
                continue;
            }
            $split             = preg_split('/\s*:\s*/', $declaration);
            $styles[$split[0]] = $split[1];
        }

        return $styles;
    }

    /**
     * Parses CSS content into an associative 2D array of all selectors and
     * their respective style declarations.
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     *
     * @param string $css The CSS style rules.
     *
     * @return string[][] A 2D associative array with style declarations.
     */
    public static function parseCss(string $css): array
    {
        $result = [];
        preg_match_all('/(?ims)([a-z0-9\s\,\.\:#_\-@^*()\[\]\"\'=]+)\{([^\}]*)\}/', $css, $arr);

        foreach ($arr[0] as $i => $x) {
            $selectors = array_map(function (string $selector) {
                return Str::trim($selector);
            }, explode(',', Str::trim($arr[1][$i])));
            if (in_array($selectors[0], ['@font-face', '@keyframes', '@media'])) {
                continue;
            }
            $rules = self::parseStyles(Str::trim($arr[2][$i]));
            foreach ($selectors as $selector) {
                $result[$selector] = array_merge($result[$selector] ?? [], $rules);
            }
        }

        return $result;
    }
}
