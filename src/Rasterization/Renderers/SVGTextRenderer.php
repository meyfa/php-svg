<?php
namespace SVG\Rasterization\Renderers;

use SVG\Rasterization\SVGRasterizer;

class SVGTextRenderer extends SVGRenderer
{
    protected function prepareRenderParams(SVGRasterizer $rasterizer, array $options)
    {
        $options['x'] += $rasterizer->getOffsetX();
        $options['y'] += $rasterizer->getOffsetY();

        return $options;
    }

    /**
     * @SuppressWarnings("unused")
     */
    protected function renderFill($image, array $params, $color)
    {
        imagettftext(
            $image,
            $params['size'],
            0,
            $params['x'],
            $params['y'],
            $color,
            $params['font_path'],
            $params['text']
        );
    }

    protected function renderStroke($image, array $params, $color, $strokeWidth)
    {
        $x = $params['x'];
        $y = $params['y'];
        $px = $strokeWidth;

        for ($c1 = ($x-abs($px)); $c1 <= ($x+abs($px)); $c1++) {
            for ($c2 = ($y - abs($px)); $c2 <= ($y + abs($px)); $c2++) {
                imagettftext($image, $params['size'], 0, $c1, $c2, $color, $params['font_path'], $params['text']);
            }
        }
    }
}
