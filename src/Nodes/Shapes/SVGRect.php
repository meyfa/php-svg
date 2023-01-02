<?php

namespace SVG\Nodes\Shapes;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;
use SVG\Rasterization\Transform\TransformParser;
use SVG\Utilities\Units\Length;

/**
 * Represents the SVG tag 'rect'.
 * Has the special attributes x, y, width, height, rx, ry.
 */
class SVGRect extends SVGNodeContainer
{
    const TAG_NAME = 'rect';

    /**
     * @param mixed $x      The x coordinate of the upper left corner.
     * @param mixed $y      The y coordinate of the upper left corner.
     * @param mixed $width  The width.
     * @param mixed $height The height.
     */
    public function __construct($x = null, $y = null, $width = null, $height = null)
    {
        parent::__construct();

        $this->setAttribute('x', $x);
        $this->setAttribute('y', $y);
        $this->setAttribute('width', $width);
        $this->setAttribute('height', $height);
    }

    /**
     * @return string|null The x coordinate of the upper left corner.
     */
    public function getX(): ?string
    {
        return $this->getAttribute('x');
    }

    /**
     * Sets the x coordinate of the upper left corner.
     *
     * @param mixed $x The new coordinate.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setX($x): SVGRect
    {
        return $this->setAttribute('x', $x);
    }

    /**
     * @return string|null The y coordinate of the upper left corner.
     */
    public function getY(): ?string
    {
        return $this->getAttribute('y');
    }

    /**
     * Sets the y coordinate of the upper left corner.
     *
     * @param mixed $y The new coordinate.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setY($y): SVGRect
    {
        return $this->setAttribute('y', $y);
    }

    /**
     * @return string|null The width.
     */
    public function getWidth(): ?string
    {
        return $this->getAttribute('width');
    }

    /**
     * @param mixed $width The new width.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setWidth($width): SVGRect
    {
        return $this->setAttribute('width', $width);
    }

    /**
     * @return string|null The height.
     */
    public function getHeight(): ?string
    {
        return $this->getAttribute('height');
    }

    /**
     * @param mixed $height The new height.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setHeight($height): SVGRect
    {
        return $this->setAttribute('height', $height);
    }

    /**
     * @return string|null The x radius of the corners.
     */
    public function getRX(): ?string
    {
        return $this->getAttribute('rx');
    }

    /**
     * Sets the x radius of the corners.
     *
     * @param mixed $rx The new radius.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setRX($rx): SVGRect
    {
        return $this->setAttribute('rx', $rx);
    }

    /**
     * @return string|null The y radius of the corners.
     */
    public function getRY(): ?string
    {
        return $this->getAttribute('ry');
    }

    /**
     * Sets the y radius of the corners.
     *
     * @param mixed $ry The new radius.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setRY($ry): SVGRect
    {
        return $this->setAttribute('ry', $ry);
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

        $rasterizer->render('rect', [
            'x'         => Length::convert($this->getX(), $rasterizer->getDocumentWidth()),
            'y'         => Length::convert($this->getY(), $rasterizer->getDocumentHeight()),
            'width'     => Length::convert($this->getWidth(), $rasterizer->getDocumentWidth()),
            'height'    => Length::convert($this->getHeight(), $rasterizer->getDocumentHeight()),
            'rx'        => Length::convert($this->getRX(), $rasterizer->getDocumentWidth()),
            'ry'        => Length::convert($this->getRY(), $rasterizer->getDocumentHeight()),
        ], $this);

        $rasterizer->popTransform();
    }
}
