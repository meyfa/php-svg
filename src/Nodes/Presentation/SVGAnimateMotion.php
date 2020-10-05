<?php

namespace SVG\Nodes\Presentation;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'animateMotion'.
 */
class SVGAnimateMotion extends SVGNodeContainer
{
    const TAG_NAME = 'animateMotion';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer)
    {
        // Nothing to rasterize.
    }
}
