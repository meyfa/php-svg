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
     * @param mixed $cx The center's x coordinate.
     * @param mixed $cy The center's y coordinate.
     * @param mixed $r  The radius.
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
    public function getCenterX(): ?string
    {
        return $this->getAttribute('cx');
    }

    /**
     * Sets the center's x coordinate.
     *
     * @param mixed $cx The new coordinate.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setCenterX($cx): SVGCircle
    {
        return $this->setAttribute('cx', $cx);
    }

    /**
     * @return string|null The center's y coordinate.
     */
    public function getCenterY(): ?string
    {
        return $this->getAttribute('cy');
    }

    /**
     * Sets the center's y coordinate.
     *
     * @param mixed $cy The new coordinate.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setCenterY($cy): SVGCircle
    {
        return $this->setAttribute('cy', $cy);
    }

    /**
     * @return string|null The radius.
     */
    public function getRadius(): ?string
    {
        return $this->getAttribute('r');
    }

    /**
     * Sets the radius.
     *
     * @param mixed $r The new radius.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setRadius($r): SVGCircle
    {
        return $this->setAttribute('r', $r);
    }

    /**
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer): void
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
