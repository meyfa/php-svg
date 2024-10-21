<?php

namespace SVG\Nodes\Structures;

use SVG\Nodes\Embedded\SVGImage;
use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;
use SVG\Rasterization\Transform\TransformParser;

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
     * @return string|null The referenced element.
     */
    public function getHref(): ?string
    {
        return $this->getAttribute('xlink:href') ?: $this->getAttribute('href');
    }

    /**
     * Sets the element reference.
     *
     * @param string|null $href The new element reference.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setHref(?string $href): SVGUse
    {
        return $this->setAttribute('xlink:href', $href);
    }

    /**
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer): void
    {
        $element = $this->getHref();
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
