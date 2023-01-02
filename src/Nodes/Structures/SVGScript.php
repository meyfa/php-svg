<?php

namespace SVG\Nodes\Structures;

use SVG\Nodes\CDataContainer;
use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'script'.
 */
class SVGScript extends SVGNodeContainer implements CDataContainer
{
    const TAG_NAME = 'script';

    /**
     * @param string $content The script content.
     */
    public function __construct(string $content = '')
    {
        parent::__construct();

        $this->setValue($content);
    }

    /**
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer): void
    {
    }
}
