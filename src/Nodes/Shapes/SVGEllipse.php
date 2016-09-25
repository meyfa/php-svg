<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\SVG;
use JangoBrick\SVG\SVGRenderingHelper;

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

        $cx = ($offsetX + $this->cx) * $scaleX;
        $cy = ($offsetY + $this->cy) * $scaleY;
        $rx = ($this->rx) * $scaleX;
        $ry = ($this->ry) * $scaleY;

        $fill = $this->getComputedStyle('fill');
        if (isset($fill) && $fill !== 'none') {
            $fillColor = SVG::parseColor($fill, true);
            $rh->fillEllipse($cx, $cy, $rx, $ry, $fillColor);
        }

        $stroke = $this->getComputedStyle('stroke');
        if (isset($stroke) && $stroke !== 'none') {
            $strokeColor = SVG::parseColor($stroke, true);
            $rh->setStrokeWidth(SVG::convertUnit($this->getComputedStyle('stroke-width'), $ow) * $scaleX);
            $rh->drawEllipse($cx, $cy, $rx, $ry, $strokeColor);
        }

        $rh->pop();
    }
}
