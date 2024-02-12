<?php

namespace SVG\Rasterization;

use InvalidArgumentException;
use SVG\SVG;

class RasterizerFactory
{
    /**
     * @var ?string
     */
    private static $rasterizerClass = null;


    public function create(
        int $width,
        int $height,
        SVG $context,
        ?string $rasterizerClass,
        ?string $background
    ): BaseRasterizer {
        $rasterizerClass = $rasterizerClass ?? $this::$rasterizerClass ?? $this->getOptimalRasterizerClass();
        self::validateRasterizer($rasterizerClass);

        $docWidth = $context->getDocument()->getWidth();
        $docHeight = $context->getDocument()->getHeight();
        $viewBox = $context->getDocument()->getViewBox();
        $fontRegistry = $context::getFontRegistry();

        /** @var BaseRasterizer $rasterizer */
        $rasterizer = new $rasterizerClass($docWidth, $docHeight, $viewBox, $width, $height, $background);
        $rasterizer->setFontRegistry($fontRegistry);

        return $rasterizer;
    }

    private function getOptimalRasterizerClass(): string
    {
        //TODO Place holder, should determine whether Imagick is available.
        return SVGRasterizer::class;
    }

    public static function setRasterizer(string $rasterizer)
    {
        self::validateRasterizer($rasterizer);
        self::$rasterizerClass = $rasterizer;
    }

    /**
     * @param string $rasterizer
     * @return void
     */
    private static function validateRasterizer(string $rasterizer)
    {
        if (!(is_a($rasterizer, BaseRasterizer::class, true))) {
            throw new InvalidArgumentException("[$rasterizer] is not a rasterizer.");
        }
    }
}
