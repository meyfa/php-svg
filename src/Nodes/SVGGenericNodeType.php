<?php

namespace SVG\Nodes;

use SVG\Rasterization\SVGRasterizer;

/**
 * NOT INTENDED FOR USER ACCESS. This is the class that gets instantiated for
 * unknown nodes in input SVG.
 */
class SVGGenericNodeType extends SVGNodeContainer
{
    private $tagName;

    public function __construct(string $tagName)
    {
        parent::__construct();
        $this->tagName = $tagName;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->tagName;
    }

    /**
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer): void
    {
        // do nothing
    }
}
