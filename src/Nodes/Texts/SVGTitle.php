<?php
namespace SVG\Nodes\Texts;
use SVG\Nodes\SVGNode;
use SVG\Rasterization\SVGRasterizer;
/**
 * Represents the SVG tag 'title'.
 * Has the attribute 'text'.
 */
class SVGTitle extends SVGNode
{
    const TAG_NAME = 'title';

    /**
     * Dummy implementation
     *
     * @param SVGRasterizer $rasterizer
     */
    public function rasterize(SVGRasterizer $rasterizer)
    {
        // nothing to rasterize
    }
}
