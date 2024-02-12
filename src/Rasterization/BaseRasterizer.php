<?php

namespace SVG\Rasterization;

use SVG\Fonts\FontRegistry;
use SVG\Nodes\Structures\SVGDocumentFragment;
use SVG\Utilities\Units\Length;

abstract class BaseRasterizer
{
    protected $fontRegistry;
    /**
     * @var int $width The output image width, in pixels.
     */
    protected $width;
    protected $docWidth;
    protected $docHeight;
    /**
     * @var int $height The output image height, in pixels.
     */
    protected $height;

    public function __construct(?string $docWidth, ?string $docHeight, int $width, int $height)
    {
//        $this->width = $width;
//        $this->height = $height;
//
//        // precompute properties
//        $this->docWidth = Length::convert($docWidth ?: '100%', $width);
//        $this->docHeight = Length::convert($docHeight ?: '100%', $height);
    }

    abstract public function rasterize(SVGDocumentFragment $document): BaseRasterImage;

    public function setFontRegistry(FontRegistry $fontRegistry): void
    {
        $this->fontRegistry = $fontRegistry;
    }

    public function getFontRegistry(): ?FontRegistry
    {
        return $this->fontRegistry;
    }
}
