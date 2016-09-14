<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\SVG;
use JangoBrick\SVG\SVGRenderingHelper;

class SVGPolyline extends SVGNode
{
    private $points;

    public function __construct($points = [])
    {
        $this->points = $points;
        parent::__construct();
    }

    public function addPoint($a, $b = null)
    {
        if (!is_array($a)) {
            $a = [$a, $b];
        }

        $this->points[] = $a;
    }

    public function removePoint($index)
    {
        array_splice($this->points, $index, 1);
    }

    public function countPoints()
    {
        return count($this->points);
    }

    public function getPoints()
    {
        return $this->points;
    }

    public function getPoint($index)
    {
        return $this->points[$index];
    }

    public function setPoint($index, $point)
    {
        $this->points[$index] = $point;
    }

    public function toXMLString()
    {
        $s  = '<polyline';

        $s .= ' points="';
        for ($i = 0, $n = count($this->points); $i < $n; ++$i) {
            $point = $this->points[$i];
            if ($i > 0) {
                $s .= ' ';
            }
            $s .= $point[0].','.$point[1];
        }
        $s .= '"';

        if (!empty($this->styles)) {
            $s .= ' style="';
            foreach ($this->styles as $style => $value) {
                $s .= $style.': '.$value.'; ';
            }
            $s .= '"';
        }

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

        $p  = [];
        $np = count($this->points);

        for ($i = 0; $i < $np; ++$i) {
            $point = $this->points[$i];
            $p[]   = ($offsetX + $point[0]) * $scaleX;
            $p[]   = ($offsetY + $point[1]) * $scaleY;
        }

        $fill = $this->getComputedStyle('fill');
        if (isset($fill) && $fill !== 'none') {
            $fillColor = SVG::parseColor($fill, true);
            $rh->fillPolygon($p, $np, $fillColor);
        }

        $stroke = $this->getComputedStyle('stroke');
        if (isset($stroke) && $stroke !== 'none') {
            $strokeColor = SVG::parseColor($stroke, true);
            $rh->setStrokeWidth(SVG::convertUnit($this->getComputedStyle('stroke-width'), $ow) * $scaleX);
            $rh->drawPolyline($p, $np, $strokeColor);
        }

        $rh->pop();
    }
}
