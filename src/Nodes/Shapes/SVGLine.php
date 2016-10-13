<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\Rasterization\SVGRasterizer;

class SVGLine extends SVGNode
{
    private $x1, $y1, $x2, $y2;

    public function __construct($x1, $y1, $x2, $y2)
    {
        parent::__construct('line');

        $this->x1 = $x1;
        $this->y1 = $y1;
        $this->x2 = $x2;
        $this->y2 = $y2;
    }

    /**
     * @SuppressWarnings("NPath")
     */
    public static function constructFromAttributes($attrs)
    {
        $x1 = isset($attrs['x1']) ? $attrs['x1'] : '';
        $y1 = isset($attrs['y1']) ? $attrs['y1'] : '';
        $x2 = isset($attrs['x2']) ? $attrs['x2'] : '';
        $y2 = isset($attrs['y2']) ? $attrs['y2'] : '';

        return new self($x1, $y1, $x2, $y2);
    }

    public function getX1()
    {
        return $this->x1;
    }

    public function setX1($x1)
    {
        $this->x1 = $x1;
        return $this;
    }

    public function getY1()
    {
        return $this->y1;
    }

    public function setY1($y1)
    {
        $this->y1 = $y1;
        return $this;
    }

    public function getX2()
    {
        return $this->x2;
    }

    public function setX2($x2)
    {
        $this->x2 = $x2;
        return $this;
    }

    public function getY2()
    {
        return $this->y2;
    }

    public function setY2($y2)
    {
        $this->y2 = $y2;
        return $this;
    }

    public function getSerializableAttributes()
    {
        $attrs = parent::getSerializableAttributes();

        $attrs['x1'] = $this->x1;
        $attrs['y1'] = $this->y1;
        $attrs['x2'] = $this->x2;
        $attrs['y2'] = $this->y2;

        return $attrs;
    }

    public function rasterize(SVGRasterizer $rasterizer)
    {
        $rasterizer->render('line', array(
            'x1'    => $this->x1,
            'y1'    => $this->y1,
            'x2'    => $this->x2,
            'y2'    => $this->y2,
        ), $this);
    }
}
