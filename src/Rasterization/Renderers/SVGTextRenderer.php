<?php
namespace SVG\Rasterization\Renderers;

use SVG\Rasterization\SVGRasterizer;

class SVGTextRenderer extends SVGRenderer
{
    protected function prepareRenderParams(SVGRasterizer $rasterizer, array $options)
    {
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

    }
}
