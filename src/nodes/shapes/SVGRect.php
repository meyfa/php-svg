<?php

class SVGRect extends SVGNode {

    public function __construct($x, $y, $width, $height) {
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
    }





    public function getX() {
        return $this->x;
    }

    public function setX($x) {
        $this->x = $x;
    }



    public function getY() {
        return $this->y;
    }

    public function setY($y) {
        $this->y = $y;
    }





    public function getWidth() {
        return $this->width;
    }

    public function setWidth($width) {
        $this->width = $width;
    }



    public function getHeight() {
        return $this->height;
    }

    public function setHeight($height) {
        $this->height = $height;
    }





    public function toXMLString() {

        $s  = '<rect';

        $s .= ' x="'.$this->x.'"';
        $s .= ' y="'.$this->y.'"';
        $s .= ' width="'.$this->width.'"';
        $s .= ' height="'.$this->height.'"';

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

        $rx1 = ($offsetX + $this->x) * $scaleX;
        $ry1 = ($offsetY + $this->y) * $scaleY;
        $rx2 = ($offsetX + $this->x + $this->width) * $scaleX - 1;
        $ry2 = ($offsetY + $this->y + $this->height) * $scaleY - 1;

        $fill = $this->getComputedStyle('fill');
        if (isset($fill) && $fill !== 'none') {
            $fillColor = SVG::parseColor($fill, true);
            imagefilledrectangle($image, $rx1, $ry1, $rx2, $ry2, $fillColor);
        }

        $stroke = $this->getComputedStyle('stroke');
        if (isset($stroke) && $stroke !== 'none') {
            $strokeColor = SVG::parseColor($stroke, true);
            imagesetthickness($image, SVG::convertUnit($this->getComputedStyle('stroke-width'), $imageWidth / $scaleX) * $scaleX);
            imagerectangle($image, $rx1, $ry1, $rx2, $ry2, $strokeColor);
        }

    }

}