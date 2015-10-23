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

        $ecx = ($offsetX + $this->cx) * $scaleX;
        $ecy = ($offsetY + $this->cy) * $scaleY;
        $ew = ($this->rx * 2) * $scaleX;
        $eh = ($this->ry * 2) * $scaleY;

        $fill = $this->getComputedStyle('fill');
        if (isset($fill) && $fill !== 'none') {
            $fillColor = SVG::parseColor($fill, true);
            imagefilledellipse($image, $ecx, $ecy, $ew, $eh, $fillColor);
        }

        $stroke = $this->getComputedStyle('stroke');
        if (isset($stroke) && $stroke !== 'none') {
            $strokeColor = SVG::parseColor($stroke, true);
            imagesetthickness($image, SVG::convertUnit($this->getComputedStyle('stroke-width'), $imageWidth / $scaleX) * $scaleX);
            // imageellipse ignores imagesetthickness; draw arc instead
            imagearc($image, $ecx, $ecy, $ew, $eh, 0, 360, $strokeColor);
        }

    }

}