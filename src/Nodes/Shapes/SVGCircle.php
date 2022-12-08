<?php

namespace SVG\Nodes\Shapes;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;
use SVG\Rasterization\Transform\TransformParser;
use SVG\Utilities\Units\Length;

/**
 * Represents the SVG tag 'circle'.
 * Has the special attributes cx, cy, r.
 */
class SVGCircle extends SVGNodeContainer
{
    const TAG_NAME = 'circle';

    /**
     * @param string|null $cx The center's x coordinate.
     * @param string|null $cy The center's y coordinate.
     * @param string|null $r  The radius.
     */
    public function __construct($cx = null, $cy = null, $r = null)
    {
        parent::__construct();

        $this->setAttribute('cx', $cx);
        $this->setAttribute('cy', $cy);
        $this->setAttribute('r', $r);
    }

    /**
     * @return string|null The center's x coordinate.
     */
    public function getCenterX()
    {
        return $this->getAttribute('cx');
    }

    /**
     * Sets the center's x coordinate.
     *
     * @param string $cx The new coordinate.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setCenterX($cx)
    {
        return $this->setAttribute('cx', $cx);
    }

    /**
     * @return string|null The center's y coordinate.
     */
    public function getCenterY()
    {
        return $this->getAttribute('cy');
    }

    /**
     * Sets the center's y coordinate.
     *
     * @param string $cy The new coordinate.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setCenterY($cy)
    {
        return $this->setAttribute('cy', $cy);
    }

    /**
     * @return string|null The radius.
     */
    public function getRadius()
    {
        return $this->getAttribute('r');
    }

    /**
     * Sets the radius.
     *
     * @param string $r The new radius.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setRadius($r)
    {
        return $this->setAttribute('r', $r);
    }

    /**
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer)
    {
        if ($this->getComputedStyle('display') === 'none') {
            return;
        }

        $visibility = $this->getComputedStyle('visibility');
        if ($visibility === 'hidden' || $visibility === 'collapse') {
            return;
        }

        TransformParser::parseTransformString($this->getAttribute('transform'), $rasterizer->pushTransform());

        // https://svgwg.org/svg2-draft/geometry.html#R
        // Percentages: refer to the normalized diagonal of the current SVG viewport
        $r = Length::convert($this->getRadius(), $rasterizer->getNormalizedDiagonal());

        $rasterizer->render('ellipse', [
            'cx'    => Length::convert($this->getCenterX(), $rasterizer->getDocumentWidth()),
            'cy'    => Length::convert($this->getCenterY(), $rasterizer->getDocumentHeight()),
            'rx'    => $r,
            'ry'    => $r,
        ], $this);

        $rasterizer->popTransform();
    }
}
