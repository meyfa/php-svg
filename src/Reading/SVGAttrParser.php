<?php

namespace JangoBrick\SVG\Reading;


abstract class SVGAttrParser
{
    /**
     * Parses viewBox attribute to array properties
     * @param $viewBoxAttr The viewBox attribute string
     * @return array|null The array containing the viewbox 4 properties x, y, width, height
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
