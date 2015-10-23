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





    public function draw($image, $imageWidth, $imageHeight, $scaleX, $scaleY, $offsetX = 0, $offsetY = 0) {

        $ecx = ($offsetX + $this->cx) * $scaleX;
        $ecy = ($offsetY + $this->cy) * $scaleY;
        $ew = ($this->r * 2) * $scaleX;
        $eh = ($this->r * 2) * $scaleY;

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