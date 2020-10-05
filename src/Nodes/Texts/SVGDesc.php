<?php

namespace SVG\Nodes\Texts;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'desc'.
 */
class SVGDesc extends SVGNodeContainer
{
    const TAG_NAME = 'desc';

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
