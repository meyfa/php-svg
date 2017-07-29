<?php

namespace SVG\Reading;


/**
 * Class with utility methods for parsing attributes such as viewBox.
 * Meant for static access.
 */
abstract class SVGAttrParser
{
    /**
     * Parses the given string as a viewBox attribute value, resulting in an
     * array with float components (x, y, width, height).
     *
     * @param string $viewBoxAttr The viewBox attribute string.
     *
     * @return float[]|null The parsed array.
     */
    public static function parseViewBox($viewBoxAttr)
    {
        if (!empty($viewBoxAttr)) {
            $svgViewBox = explode(' ', $viewBoxAttr);
            if (count($svgViewBox) < 4) {
                $svgViewBox = explode(',', $viewBoxAttr);
            }
            if (count($svgViewBox) === 4) {
                return array_map('floatval', $svgViewBox);
            }
        }
        return null;
    }
}
