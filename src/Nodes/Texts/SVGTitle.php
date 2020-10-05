<?php

namespace SVG\Nodes\Texts;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'title'.
 */
class SVGTitle extends SVGNodeContainer
{
    const TAG_NAME = 'title';

    public function __construct($text = '')
    {
        parent::__construct();
        $this->setValue($text);
    }

    /**
     * Dummy implementation
     *
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer)
    {
        // nothing to rasterize
    }
}
