<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'polygon'.
 * Offers methods for manipulating the list of points.
 */
class SVGPolygon extends SVGPolygonalShape
{
    /**
     * @param array[] $points Array of points (float 2-tuples).
     */
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
