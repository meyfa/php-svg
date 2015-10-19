<?php

class SVGPolygon extends SVGNode {

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

        $s  = '<polygon';

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

}