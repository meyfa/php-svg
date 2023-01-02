<?php

namespace SVG\Nodes\Embedded;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'foreignObject'.
 */
class SVGForeignObject extends SVGNodeContainer
{
    const TAG_NAME = 'foreignObject';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer): void
    {
        // Nothing to rasterize.
    }
}
