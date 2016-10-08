<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\Rasterization\SVGRasterizer;

class SVGPolyline extends SVGPolygonalShape
{
    public function __construct($points = array())
    {
        parent::__construct('polyline', $points);
    }

    public function rasterize(SVGRasterizer $rasterizer)
    {
        $rasterizer->render('polygon', array(
            'open'      => true,
            'points'    => $this->getPoints(),
        ), $this);
    }
}
