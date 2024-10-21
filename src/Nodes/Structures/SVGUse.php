<?php

namespace SVG\Nodes\Structures;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'use'.
 */
class SVGUse extends SVGNodeContainer
{
    public const TAG_NAME = 'use';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer): void
    {
        $element = $this->getAttribute('xlink:href');
        if(empty($element)) return;
        
        /** @var SVGDocumentFragment $root */
        do {
            $root = $this->getParent();
        } while ($root->getParent() != null);
        $element = $root->getElementById(substr($element, strpos($element, "#") + 1 ?: 0));
        if(!$element) return;

        TransformParser::parseTransformString($this->getAttribute('transform'), $rasterizer->pushTransform());
        
        $element->rasterize($rasterizer);
        
        $rasterizer->popTransform();
    }
}
