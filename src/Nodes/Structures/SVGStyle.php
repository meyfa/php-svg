<?php

namespace SVG\Nodes\Structures;

use SVG\Nodes\CDataContainer;
use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'style'.
 * Has the attribute 'type' and the CSS content.
 */
class SVGStyle extends SVGNodeContainer implements CDataContainer
{
    const TAG_NAME = 'style';

    /**
     * @param string $css   The CSS data rules.
     * @param string $type  The style type attribute.
     */
    public function __construct(string $css = '', string $type = 'text/css')
    {
        parent::__construct();

        $this->setValue($css);
        $this->setType($type);
    }

    /**
     * @return string|null The type attribute.
     */
    public function getType(): ?string
    {
        return $this->getAttribute('type');
    }

    /**
     * @param $type string|null The type attribute.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setType(?string $type): SVGStyle
    {
        return $this->setAttribute('type', $type);
    }

    /**
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer): void
    {
    }
}
