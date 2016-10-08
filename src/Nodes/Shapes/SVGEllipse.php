<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\Rasterization\SVGRasterizer;

class SVGEllipse extends SVGNode
{
    private $cx, $cy, $rx, $ry;

    public function __construct($cx, $cy, $rx, $ry)
    {
        parent::__construct();

        $this->cx = $cx;
        $this->cy = $cy;
        $this->rx = $rx;
        $this->ry = $ry;
    }

    public function getCenterX()
    {
        return $this->cx;
    }

    public function setCenterX($cx)
    {
        $this->cx = $cx;
        return $this;
    }

    public function getCenterY()
    {
        return $this->cy;
    }

    public function setCenterY($cy)
    {
        $this->cy = $cy;
        return $this;
    }

    public function getRadiusX()
    {
        return $this->rx;
    }

    public function setRadiusX($rx)
    {
        $this->rx = $rx;
        return $this;
    }

    public function getRadiusY()
    {
        return $this->ry;
    }

    public function setRadiusY($ry)
    {
        $this->ry = $ry;
        return $this;
    }

    public function toXMLString()
    {
        $s  = '<ellipse';

        $s .= ' cx="'.$this->cx.'"';
        $s .= ' cy="'.$this->cy.'"';
        $s .= ' rx="'.$this->rx.'"';
        $s .= ' ry="'.$this->ry.'"';

        $this->addStylesToXMLString($s);
        $this->addAttributesToXMLString($s);

        $s .= ' />';

        return $s;
    }

    public function rasterize(SVGRasterizer $rasterizer)
    {
        $rasterizer->render('ellipse', array(
            'cx'    => $this->cx,
            'cy'    => $this->cy,
            'rx'    => $this->rx,
            'ry'    => $this->ry,
        ), $this);
    }
}
