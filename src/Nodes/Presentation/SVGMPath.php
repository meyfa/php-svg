<?php

namespace SVG\Nodes\Presentation;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'mpath'.
 */
class SVGMPath extends SVGNodeContainer
{
    const TAG_NAME = 'mpath';

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
