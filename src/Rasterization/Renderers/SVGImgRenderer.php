<?php

/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 2/3/17
 */

namespace JangoBrick\SVG\Rasterization\Renderers;

use JangoBrick\SVG\Rasterization\SVGRasterizer;
use JangoBrick\SVG\SVGImage;

class SVGImgRenderer extends SVGRenderer
{
    /**
     * Converts the options array into a new parameters array that the render
     * methods can make more sense of.
     *
     * Specifically, the intention is to allow subclasses to outsource
     * coordinate translation, approximation of curves and the like to this
     * method rather than dealing with it in the render methods. This shall
     * encourage single passes over the input data (for performance reasons).
     *
     * @param SVGRasterizer $rasterizer The rasterizer used in this render
     * @param mixed[]       $options    The associative array of raw options
     *
     * @return mixed[] The new associative array of computed render parameters
     */
    protected function prepareRenderParams(SVGRasterizer $rasterizer, array $options)
    {
        $x = self::prepareLengthX($options['x'], $rasterizer) + $rasterizer->getOffsetX();
        $y = self::prepareLengthY($options['y'], $rasterizer) + $rasterizer->getOffsetY();
        $w = self::prepareLengthX($options['width'], $rasterizer);
        $h = self::prepareLengthY($options['height'], $rasterizer);

        return array(
            'href' => $options['href'],
            'x' => $x,
            'y' => $y,
            'width' => $w,
            'height' => $h,
        );
    }

    /**
     * Renders the shape's filled version in the given color, using the params
     * array obtained from the prepare method.
     *
     * Doing nothing is valid behavior if the shape can't be filled
     * (for example, a line).
     *
     * @see SVGRenderer::prepareRenderParams() For info on the params array
     *
     * @param resource $image  The image resource to render to
     * @param mixed[]  $params The render params
     * @param int      $color  The color (a GD int) to fill the shape with
     */
    protected function renderFill($image, array $params, $color)
    {

        $imgHref = $params['href'];
        $im = null;
        $format = '';
        if (!preg_match('/data:([^;]*);([a-zA-Z0-9 ]*),(.*)/', $imgHref, $matches)) {
            // If image is directly encoded inside href
            $format = trim($matches[1]);
            $encode = trim($matches[2]);
            $content = trim($matches[3]);
            // If content is base64 encoded, decode content
            if ($encode === "base64") {
                $content = base64_decode($content);
            }
        } else {
            // Else if image is url or path try to guess if it's svg or image and load it
            $content = file_get_contents($imgHref);
        }
        // If image is svg then create svg and rasterize it, otherwise create image from content
        if (
            strpos($format, 'svg') !== false ||
            (preg_match('/^<\?xml/', $content) && strpos($content, '<svg') !== false)
        ) {
            $svg = SVGImage::fromString($content);
            $im = $svg->toRasterImage($params['width'], $params['height']);
        } else {
            $im = imagecreatefromstring($content);
        }
        if ($im !== null) {
            imagecopyresized(
                $image,
                $im,
                $params['x'],
                $params['y'],
                0,
                0,
                $params['width'],
                $params['height'],
                imagesx($im),
                imagesy($im)
            );
        }
    }

    /**
     * Renders the shape's outline in the given color, using the params array
     * obtained from the prepare method.
     *
     * @see SVGRenderer::prepareRenderParams() For info on the params array
     *
     * @param resource $image       The image resource to render to
     * @param mixed[]  $params      The render params
     * @param int      $color       The color (a GD int) to outline the shape with
     * @param float    $strokeWidth The stroke's thickness, in pixels
     */
    protected function renderStroke($image, array $params, $color, $strokeWidth)
    {
        return;
    }
}
