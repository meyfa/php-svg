<?php

namespace SVG\Nodes\Structures;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'clipPath'.
 */
class SVGClipPath extends SVGNodeContainer
{
    const TAG_NAME = 'clipPath';

    public function __construct(string $id = null)
    {
        parent::__construct();

        $this->setAttribute('id', $id);
    }

    /**
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer): void
    {
        // TODO How do we rasterize this? The clipPath in and of itself wont get rasterized, but usages of it will be!
    }
}
