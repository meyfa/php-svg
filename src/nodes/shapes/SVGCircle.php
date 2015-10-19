<?php

class SVGCircle extends SVGNode {

    private $cx, $cy, $r;



    public function __construct($cx, $cy, $r) {
        $this->cx = $cx;
        $this->cy = $cy;
        $this->r = $r;
    }





    public function getCenterX() {
        return $this->cx;
    }

    public function setCenterX($cx) {
        $this->cx = $cx;
    }



    public function getCenterY() {
        return $this->cy;
    }

    public function setCenterY($cy) {
        $this->cy = $cy;
    }





    public function getRadius() {
        return $this->r;
    }

    public function setRadius($r) {
        $this->r = $r;
    }





    public function toXMLString() {

        $s  = '<circle';

        $s .= ' cx="'.$this->cx.'"';
        $s .= ' cy="'.$this->cy.'"';
        $s .= ' r="'.$this->r.'"';

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