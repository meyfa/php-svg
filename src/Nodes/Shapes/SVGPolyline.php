<?php

namespace SVG\Nodes\Shapes;

use SVG\Rasterization\SVGRasterizer;
use SVG\Rasterization\Transform\TransformParser;

/**
 * Represents the SVG tag 'polyline'.
 * Offers methods for manipulating the list of points.
 */
class SVGPolyline extends SVGPolygonalShape
{
    const TAG_NAME = 'polyline';

    /**
     * @param array[] $points Array of points (float 2-tuples).
     */
    public function __construct($points = [])
    {
        parent::__construct($points);
    }

    /**
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer): void
    {
        if ($this->getComputedStyle('display') === 'none') {
            return;
        }

        $visibility = $this->getComputedStyle('visibility');
        if ($visibility === 'hidden' || $visibility === 'collapse') {
            return;
        }

        TransformParser::parseTransformString($this->getAttribute('transform'), $rasterizer->pushTransform());

        $rasterizer->render('polygon', [
            'open'      => true,
            'points'    => $this->getPoints(),
            'fill-rule' => strtolower($this->getComputedStyle('fill-rule') ?: 'nonzero')
        ], $this);

        $rasterizer->popTransform();
    }
}
