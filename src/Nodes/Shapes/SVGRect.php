<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\Rasterization\SVGRasterizer;

class SVGRect extends SVGNode
{
    protected $x, $y, $width, $height;

    public function __construct($x, $y, $width, $height)
    {
        parent::__construct();

        $this->x      = $x;
        $this->y      = $y;
        $this->width  = $width;
        $this->height = $height;
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

    public function toXMLString()
    {
        $s  = '<rect';

        $s .= ' x="'.$this->x.'"';
        $s .= ' y="'.$this->y.'"';
        $s .= ' width="'.$this->width.'"';
        $s .= ' height="'.$this->height.'"';

        $this->addStylesToXMLString($s);
        $this->addAttributesToXMLString($s);

        $s .= ' />';

        return $s;
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
