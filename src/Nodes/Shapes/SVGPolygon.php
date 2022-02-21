<?php

namespace SVG\Nodes\Shapes;

use SVG\Rasterization\SVGRasterizer;
use SVG\Rasterization\Transform\TransformParser;

/**
 * Represents the SVG tag 'polygon'.
 * Offers methods for manipulating the list of points.
 */
class SVGPolygon extends SVGPolygonalShape
{
    const TAG_NAME = 'polygon';

    /**
     * @param array[] $points Array of points (float 2-tuples).
     */
    public function __construct($points = array())
    {
        parent::__construct($points);
    }

    /**
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer)
    {
        if ($this->getComputedStyle('display') === 'none') {
            return;
        }

        $visibility = $this->getComputedStyle('visibility');
        if ($visibility === 'hidden' || $visibility === 'collapse') {
            return;
        }

        TransformParser::parseTransformString($this->getAttribute('transform'), $rasterizer->pushTransform());

        $rasterizer->render('polygon', array(
            'open'      => false,
            'points'    => $this->getPoints(),
        ), $this);

        $rasterizer->popTransform();
    }
}
