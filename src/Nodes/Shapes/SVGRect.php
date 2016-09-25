<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\SVG;
use JangoBrick\SVG\SVGRenderingHelper;

class SVGRect extends SVGNode
{
    protected $x, $y, $width, $height;

    public function __construct($x, $y, $width, $height)
    {
        $this->x      = $x;
        $this->y      = $y;
        $this->width  = $width;
        $this->height = $height;
        parent::__construct();
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

    public function draw(SVGRenderingHelper $rh, $scaleX, $scaleY, $offsetX = 0, $offsetY = 0)
    {
        $rh->push();

        $opacity = $this->getStyle('opacity');
        if (isset($opacity) && is_numeric($opacity)) {
            $opacity = floatval($opacity);
            $rh->scaleOpacity($opacity);
        }

        // original (document fragment) width for unit parsing
        $ow = $rh->getWidth() / $scaleX;

        $x = ($offsetX + $this->x) * $scaleX;
        $y = ($offsetY + $this->y) * $scaleY;
        $w = ($this->width) * $scaleX;
        $h = ($this->height) * $scaleY;

        $fill = $this->getComputedStyle('fill');
        if (isset($fill) && $fill !== 'none') {
            $fillColor = SVG::parseColor($fill, true);
            $rh->fillRect($x, $y, $w, $h, $fillColor);
        }

        $stroke = $this->getComputedStyle('stroke');
        if (isset($stroke) && $stroke !== 'none') {
            $strokeColor = SVG::parseColor($stroke, true);
            $rh->setStrokeWidth(SVG::convertUnit($this->getComputedStyle('stroke-width'), $ow) * $scaleX);
            $rh->drawRect($x, $y, $w, $h, $strokeColor);
        }

        $rh->pop();
    }
}
