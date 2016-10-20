<?php

namespace JangoBrick\SVG\Rasterization\Renderers;

use JangoBrick\SVG\SVG;
use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\Rasterization\SVGRasterizer;

abstract class SVGRenderer
{
    public function render(SVGRasterizer $rasterizer, array $options, SVGNode $context)
    {
        $params = $this->prepareRenderParams($rasterizer, $options);

        $image = $rasterizer->getImage();

        $fill = $context->getComputedStyle('fill');
        if (isset($fill) && $fill !== 'none') {
            $fill = self::prepareColor($fill, $context);

            $this->renderFill($image, $params, $fill);
        }

        $stroke = $context->getComputedStyle('stroke');
        if (isset($stroke) && $stroke !== 'none') {
            $stroke      = self::prepareColor($stroke, $context);
            $strokeWidth = $context->getComputedStyle('stroke-width');
            $strokeWidth = self::prepareLength($strokeWidth, $rasterizer);

            $this->renderStroke($image, $params, $stroke, $strokeWidth);
        }
    }



    private static function prepareColor($color, SVGNode $context)
    {
        $color = SVG::parseColor($color);
        $rgb   = ($color[0] << 16) + ($color[1] << 8) + ($color[2]);

        $a = 127 - intval($color[3] * 127 / 255);
        $a = $a * self::calculateTotalOpacity($context);

        return $rgb | ($a << 24);
    }

    private static function getNodeOpacity(SVGNode $node)
    {
        $opacity = $node->getStyle('opacity');

        if (is_numeric($opacity)) {
            return floatval($opacity);
        } elseif ($opacity === 'inherit') {
            $parent = $node->getParent();
            if (isset($parent)) {
                return self::getNodeOpacity($parent);
            }
        }

        return 1;
    }

    private static function calculateTotalOpacity(SVGNode $node)
    {
        $opacity = self::getNodeOpacity($node);

        $parent  = $node->getParent();
        if (isset($parent)) {
            return $opacity * self::calculateTotalOpacity($parent);
        }

        return $opacity;
    }



    private static function prepareLength($len, SVGRasterizer $rasterizer)
    {
        $docWidth = $rasterizer->getDocumentWidth();
        $scaleX   = $rasterizer->getScaleX();

        return SVG::convertUnit($len, $docWidth) * $scaleX;
    }



    abstract protected function prepareRenderParams(SVGRasterizer $rasterizer, array $options);

    abstract protected function renderFill($image, array $params, $color);

    abstract protected function renderStroke($image, array $params, $color, $strokeWidth);
}
