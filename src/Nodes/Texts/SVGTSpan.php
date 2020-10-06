<?php

namespace SVG\Nodes\Texts;

use SVG\Nodes\SVGNodeContainer;
use SVG\Nodes\Structures\SVGFont;
use SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'tspan'.
 */
class SVGTSpan extends SVGNodeContainer
{
    const TAG_NAME = 'tspan';

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
