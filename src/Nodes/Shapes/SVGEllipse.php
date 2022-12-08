<?php

namespace SVG\Nodes\Shapes;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;
use SVG\Rasterization\Transform\TransformParser;
use SVG\Utilities\Units\Length;

/**
 * Represents the SVG tag 'ellipse'.
 * Has the special attributes cx, cy, rx, ry.
 */
class SVGEllipse extends SVGNodeContainer
{
    const TAG_NAME = 'ellipse';

    /**
     * @param string|null $cx The center's x coordinate.
     * @param string|null $cy The center's y coordinate.
     * @param string|null $rx The radius along the x-axis.
     * @param string|null $ry The radius along the y-axis.
     */
    public function __construct($cx = null, $cy = null, $rx = null, $ry = null)
    {
        parent::__construct();

        $this->setAttribute('cx', $cx);
        $this->setAttribute('cy', $cy);
        $this->setAttribute('rx', $rx);
        $this->setAttribute('ry', $ry);
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
     * @return string|null The radius along the x-axis.
     */
    public function getRadiusX()
    {
        return $this->getAttribute('rx');
    }

    /**
     * Sets the radius along the x-axis.
     *
     * @param string $rx The new radius.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setRadiusX($rx)
    {
        return $this->setAttribute('rx', $rx);
    }

    /**
     * @return string|null The radius along the y-axis.
     */
    public function getRadiusY()
    {
        return $this->getAttribute('ry');
    }

    /**
     * Sets the radius along the y-axis.
     *
     * @param string $ry The new radius.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setRadiusY($ry)
    {
        return $this->setAttribute('ry', $ry);
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

        $rasterizer->render('ellipse', [
            'cx'    => Length::convert($this->getCenterX(), $rasterizer->getDocumentWidth()),
            'cy'    => Length::convert($this->getCenterY(), $rasterizer->getDocumentHeight()),
            'rx'    => Length::convert($this->getRadiusX(), $rasterizer->getDocumentWidth()),
            'ry'    => Length::convert($this->getRadiusY(), $rasterizer->getDocumentHeight()),
        ], $this);

        $rasterizer->popTransform();
    }
}
