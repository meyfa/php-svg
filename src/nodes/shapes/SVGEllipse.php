<?php

class SVGEllipse extends SVGNode {

    private $cx, $cy, $rx, $ry;



    public function __construct($cx, $cy, $rx, $ry) {
        $this->cx = $cx;
        $this->cy = $cy;
        $this->rx = $rx;
        $this->ry = $ry;
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





    public function getRadiusX() {
        return $this->rx;
    }

    public function setRadiusX($rx) {
        $this->rx = $rx;
    }



    public function getRadiusY() {
        return $this->ry;
    }

    public function setRadiusY($ry) {
        $this->ry = $ry;
    }





    public function toXMLString() {

        $s  = '<ellipse';

        $s .= ' cx="'.$this->cx.'"';
        $s .= ' cy="'.$this->cy.'"';
        $s .= ' rx="'.$this->rx.'"';
        $s .= ' ry="'.$this->ry.'"';

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

    }

}