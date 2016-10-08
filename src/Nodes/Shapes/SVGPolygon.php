<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\Rasterization\SVGRasterizer;

class SVGPolygon extends SVGPolygonalShape
{
    public function __construct($points = array())
    {
        parent::__construct('polygon', $points);
    }

    public function rasterize(SVGRasterizer $rasterizer)
    {
        $rasterizer->render('polygon', array(
            'open'      => false,
            'points'    => $this->getPoints(),
        ), $this);
    }
}
