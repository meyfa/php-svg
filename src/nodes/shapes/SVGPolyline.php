<?php

class SVGPolyline extends SVGNode {

    private $points;



    public function __construct($points = array()) {
        $this->points = $points;
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

}