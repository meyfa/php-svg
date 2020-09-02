<?php

namespace SVG\Nodes\Presentation;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'linearGradient'.
 */
class SVGLinearGradient extends SVGNodeContainer
{
    const TAG_NAME = 'linearGradient';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param SVGRasterizer $rasterizer
     */
    public function rasterize(SVGRasterizer $rasterizer)
    {
        // Nothing to rasterize.
    }
}
