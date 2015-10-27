<?php

class SVGRenderingHelper {

    private $image, $imageWidth, $imageHeight;
    private $strokeWidth;



    public function __construct($image, $imageWidth, $imageHeight) {
        $this->image = $image;
        $this->imageWidth = $imageWidth;
        $this->imageHeight = $imageHeight;
    }





    public function getWidth() {
        return $this->imageWidth;
    }

    public function getHeight() {
        return $this->imageHeight;
    }





    public function setStrokeWidth($strokeWidth) {
        $this->strokeWidth = $strokeWidth;
        imagesetthickness($this->image, $strokeWidth);
    }





    public function drawRect($x, $y, $width, $height, $color) {
        imagerectangle($this->image, $x, $y, $x + $width - 1, $y + $height - 1, $color);
    }

    public function fillRect($x, $y, $width, $height, $color) {
        imagefilledrectangle($this->image, $x, $y, $x + $width - 1, $y + $height - 1, $color);
    }





    public function drawEllipse($cx, $cy, $rx, $ry, $color) {
        // imageellipse ignores imagesetthickness; draw arc instead
        imagearc($this->image, $cx, $cy, $rx * 2, $ry * 2, 0, 360, $color);
    }

    public function fillEllipse($cx, $cy, $rx, $ry, $color) {
        imagefilledellipse($this->image, $cx, $cy, $rx * 2, $ry * 2, $color);
    }





    public function drawLine($x1, $y1, $x2, $y2, $color) {
        imageline($this->image, $x1, $y1, $x2, $y2, $color);
    }





    public function drawPolygon($points, $numpoints, $color) {
        imagepolygon($this->image, $points, $numpoints, $color);
    }

    public function fillPolygon($points, $numpoints, $color) {
        imagefilledpolygon($this->image, $points, $numpoints, $color);
    }



    public function drawPolyline($points, $numpoints, $color) {

        for ($i=1; $i<$numpoints; $i++) {
            $x1 = $points[($i-1) * 2];
            $y1 = $points[($i-1) * 2 + 1];
            $x2 = $points[$i * 2];
            $y2 = $points[$i * 2 + 1];
            imageline($this->image, $x1, $y1, $x2, $y2, $color);
        }

    }

}
