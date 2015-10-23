<?php

class SVGPolyline extends SVGNode {

    private $points;



    public function __construct($points = array()) {
        $this->points = $points;
    }





    public function addPoint($a, $b = null) {

        if (!is_array($a)) {
            $a = array($a, $b);
        }

        $points[] = $a;

    }

    public function removePoint($index) {
        array_splice($this->points, $index, 1);
    }



    public function countPoints() {
        return count($this->points);
    }



    public function getPoints() {
        return $this->points;
    }

    public function getPoint($index) {
        return $this->points[$index];
    }



    public function setPoint($index, $point) {
        $this->points[$index] = $point;
    }





    public function toXMLString() {

        $s  = '<polyline';

        $s .= ' points="';
        for ($i=0, $n=count($this->points); $i<$n; $i++) {
            $point = $this->points[$i];
            if ($i > 0)
                $s .= ' ';
            $s .= $point[0] . ',' . $point[1];
        }
        $s .= '"';

        if (!empty($this->styles)) {
            $s .= ' style="';
            foreach ($this->styles as $style => $value) {
                $s .= $style . ': ' . $value . '; ';
            }
            $s .= '"';
        }

        $s .= ' />';

        return $s;

    }





    public function draw($image, $imageWidth, $imageHeight, $scaleX, $scaleY, $offsetX = 0, $offsetY = 0) {

        $p = array();
        $np = count($this->points);

        for ($i=0; $i<$np; $i++) {
            $point = $this->points[$i];
            $p[] = ($offsetX + $point[0]) * $scaleX;
            $p[] = ($offsetY + $point[1]) * $scaleY;
        }

        $fill = $this->getComputedStyle('fill');
        if (isset($fill) && $fill !== 'none') {
            $fillColor = SVG::parseColor($fill, true);
            imagefilledpolygon($image, $p, $np, $fillColor);
        }

        $stroke = $this->getComputedStyle('stroke');
        if (isset($stroke) && $stroke !== 'none') {
            $strokeColor = SVG::parseColor($stroke, true);
            imagesetthickness($image, SVG::convertUnit($this->getComputedStyle('stroke-width'), $imageWidth / $scaleX) * $scaleX);
            for ($i=1; $i<$np; $i++) {
                $x1 = $p[($i-1) * 2];
                $y1 = $p[($i-1) * 2 + 1];
                $x2 = $p[$i * 2];
                $y2 = $p[$i * 2 + 1];
                imageline($image, $x1, $y1, $x2, $y2, $strokeColor);
            }
        }

    }

}