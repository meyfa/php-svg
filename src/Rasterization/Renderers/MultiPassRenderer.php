<?php

namespace SVG\Rasterization\Renderers;

use SVG\Nodes\SVGNode;
use SVG\Rasterization\SVGRasterizer;
use SVG\Rasterization\Transform\Transform;
use SVG\Utilities\Colors\Color;
use SVG\Utilities\Units\Length;

/**
 * This extends the Renderer class to offer features for multi-pass rendering
 * of shapes. The render options are first prepared, then given to the primitive
 * methods (stroke, fill).
 */
abstract class MultiPassRenderer extends Renderer
{
    /**
     * @inheritdoc
     */
    public function render(SVGRasterizer $rasterizer, array $options, SVGNode $context)
    {
        $transform = $rasterizer->getCurrentTransform();

        $params = $this->prepareRenderParams($options, $transform);

        $paintOrder = self::getPaintOrder($context);
        foreach ($paintOrder as $paint) {
            if ($paint === 'stroke') {
                $this->paintStroke($rasterizer, $context, $params);
            } elseif ($paint === 'fill') {
                $this->paintFill($rasterizer, $context, $params);
            }
        }
    }

    /**
     * @param SVGRasterizer $rasterizer
     * @param SVGNode $context
     * @param $params
     */
    private function paintStroke(SVGRasterizer $rasterizer, SVGNode $context, $params)
    {
        $stroke = $context->getComputedStyle('stroke');
        if (isset($stroke) && $stroke !== 'none') {
            $stroke = self::prepareColor($stroke, $context);

            $strokeWidth = $context->getComputedStyle('stroke-width');
            $strokeWidth = Length::convert($strokeWidth, $rasterizer->getNormalizedDiagonal());
            $strokeWidth = $strokeWidth * $rasterizer->getDiagonalScale();

            if ($strokeWidth > 0) {
                $this->renderStroke($rasterizer->getImage(), $params, $stroke, $strokeWidth);
            }
        }
    }

    /**
     * @param SVGRasterizer $rasterizer
     * @param SVGNode $context
     * @param $params
     */
    private function paintFill(SVGRasterizer $rasterizer, SVGNode $context, $params)
    {
        $fill = $context->getComputedStyle('fill');
        if (isset($fill) && $fill !== 'none') {
            $fill = self::prepareColor($fill, $context);

            $this->renderFill($rasterizer->getImage(), $params, $fill);
        }
    }

    /**
     * Converts the options array into a new parameters array that the render
     * methods can make more sense of.
     *
     * Specifically, the intention is to allow subclasses to outsource
     * coordinate translation, approximation of curves and the like to this
     * method rather than dealing with it in the render methods. This shall
     * encourage single passes over the input data (for performance reasons).
     *
     * @param array     $options   The associative array of raw options.
     * @param Transform $transform The coordinate transform to apply, to go from user coordinate to output coordinates.
     *
     * @return array The new associative array of computed render parameters.
     */
    abstract protected function prepareRenderParams(array $options, Transform $transform);

    /**
     * Renders the shape's filled version in the given color, using the params
     * array obtained from the prepare method.
     *
     * Doing nothing is valid behavior if the shape can't be filled
     * (for example, a line).
     *
     * @see Renderer::prepareRenderParams() For info on the params array.
     *
     * @param resource $image  The image resource to render to.
     * @param mixed[]  $params The render params.
     * @param int      $color  The color (a GD int) to fill the shape with.
     *
     * @return void
     */
    abstract protected function renderFill($image, array $params, $color);

    /**
     * Renders the shape's outline in the given color, using the params array
     * obtained from the prepare method.
     *
     * @see Renderer::prepareRenderParams() For info on the params array.
     *
     * @param resource $image  The image resource to render to.
     * @param mixed[]  $params The render params.
     * @param int      $color  The color (a GD int) to outline the shape with.
     * @param float    $strokeWidth  The stroke's thickness, in pixels.
     *
     * @return void
     */
    abstract protected function renderStroke($image, array $params, $color, $strokeWidth);

    /**
     * @param SVGNode $context
     * @return string[]
     */
    private static function getPaintOrder(SVGNode $context)
    {
        $paintOrder = $context->getComputedStyle('paint-order');
        $paintOrder = preg_replace('#\s{2,}#', ' ', trim($paintOrder));

        $defaultOrder = array('fill', 'stroke', 'markers');

        if ($paintOrder === 'normal' || empty($paintOrder)) {
            return $defaultOrder;
        }

        $paintOrder = array_intersect(explode(' ', $paintOrder), $defaultOrder);

        return array_merge($paintOrder, array_diff($defaultOrder, $paintOrder));
    }

    /**
     * Parses the color string and applies the node's total opacity to it,
     * then returns it as a GD color int.
     *
     * @param string  $color   The CSS color value.
     * @param SVGNode $context The node serving as the opacity reference.
     *
     * @return int The prepared color as a GD color integer.
     */
    private static function prepareColor($color, SVGNode $context)
    {
        $color = Color::parse($color);
        $rgb   = ($color[0] << 16) + ($color[1] << 8) + ($color[2]);

        $opacity = self::calculateTotalOpacity($context);
        $a = 127 - $opacity * (int) ($color[3] * 127 / 255);

        return $rgb | ($a << 24);
    }

    /**
     * Obtains the node's very own opacity value, as specified in its styles,
     * taking care of 'inherit' and defaulting to 1.
     *
     * @param SVGNode $node The node to get the opacity value of.
     *
     * @return float The node's own opacity value.
     */
    private static function getNodeOpacity(SVGNode $node)
    {
        $opacity = $node->getStyle('opacity');

        if (is_numeric($opacity)) {
            return (float) $opacity;
        } elseif ($opacity === 'inherit') {
            $parent = $node->getParent();
            if (isset($parent)) {
                return self::getNodeOpacity($parent);
            }
        }

        return 1;
    }

    /**
     * Calculates the node's total opacity by multiplying its own with all of
     * its parents' ones.
     *
     * @param SVGNode $node The node of which to calculate the opacity.
     *
     * @return float The node's total opacity.
     */
    private static function calculateTotalOpacity(SVGNode $node)
    {
        $opacity = self::getNodeOpacity($node);

        $parent  = $node->getParent();
        if (isset($parent)) {
            return $opacity * self::calculateTotalOpacity($parent);
        }

        return $opacity;
    }
}
