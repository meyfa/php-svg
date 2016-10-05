<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\SVGRenderingHelper;

class SVGPolygon extends SVGPolygonalShape
{
    public function __construct($points = array())
    {
        parent::__construct('polygon', $points);
    }

    protected function drawOutline(SVGRenderingHelper $rh, $points, $numPoints, $strokeColor)
    {
        $rh->drawPolygon($points, $numPoints, $strokeColor);
    }
}
