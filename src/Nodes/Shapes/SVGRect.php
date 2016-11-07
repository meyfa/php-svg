<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'rect'.
 * Has the special attributes x, y, width, height.
 */
class SVGRect extends SVGNode
{
    const TAG_NAME = 'rect';

    /**
     * @var string $x      The x coordinate of the upper left corner.
     * @var string $y      The y coordinate of the upper left corner.
     * @var string $width  The width.
     * @var string $height The height.
     */
    protected $x, $y, $width, $height;

    /**
     * @param string $x      The x coordinate of the upper left corner.
     * @param string $y      The y coordinate of the upper left corner.
     * @param string $width  The width.
     * @param string $height The height.
     */
    public function __construct($x, $y, $width, $height)
    {
        parent::__construct();

        $this->x      = $x;
        $this->y      = $y;
        $this->width  = $width;
        $this->height = $height;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings("NPath")
     */
    public static function constructFromAttributes($attrs)
    {
        $x = isset($attrs['x']) ? $attrs['x'] : 0;
        $y = isset($attrs['y']) ? $attrs['y'] : 0;
        $w = isset($attrs['width']) ? $attrs['width'] : 0;
        $h = isset($attrs['height']) ? $attrs['height'] : 0;

        return new self($x, $y, $w, $h);
    }



    /**
     * @return The x coordinate of the upper left corner.
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * Sets the x coordinate of the upper left corner.
     *
     * @param string $x The new coordinate.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setX($x)
    {
        $this->x = $x;
        return $this;
    }

    /**
     * @return The y coordinate of the upper left corner.
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Sets the y coordinate of the upper left corner.
     *
     * @param string $y The new coordinate.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setY($y)
    {
        $this->y = $y;
        return $this;
    }



    /**
     * @return The width.
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param string $width The new width.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return The height.
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param string $height The new height.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }



    public function getSerializableAttributes()
    {
        $attrs = parent::getSerializableAttributes();

        $attrs['x'] = $this->x;
        $attrs['y'] = $this->y;
        $attrs['width'] = $this->width;
        $attrs['height'] = $this->height;

        return $attrs;
    }



    public function rasterize(SVGRasterizer $rasterizer)
    {
        $rasterizer->render('rect', array(
            'x'         => $this->x,
            'y'         => $this->y,
            'width'     => $this->width,
            'height'    => $this->height,
        ), $this);
    }
}
