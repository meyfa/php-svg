<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\Rasterization\SVGRasterizer;

class SVGRect extends SVGNode
{
    protected $x, $y, $width, $height;

    public function __construct($x, $y, $width, $height)
    {
        parent::__construct('rect');

        $this->x      = $x;
        $this->y      = $y;
        $this->width  = $width;
        $this->height = $height;
    }

    /**
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

    public function getX()
    {
        return $this->x;
    }

    public function setX($x)
    {
        $this->x = $x;
        return $this;
    }

    public function getY()
    {
        return $this->y;
    }

    public function setY($y)
    {
        $this->y = $y;
        return $this;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight()
    {
        return $this->height;
    }

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
