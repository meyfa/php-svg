<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\SVGRenderingHelper;

class SVGPolyline extends SVGPolygonalShape
{
    public function __construct($points = array())
    {
        parent::__construct('polyline', $points);
    }

    protected function drawOutline(SVGRenderingHelper $rh, $points, $numPoints, $strokeColor)
    {
        $rh->drawPolyline($points, $numPoints, $strokeColor);
    }
}
