<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 2/10/17
 */

namespace JangoBrick\SVG\Utilities;


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