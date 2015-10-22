<?php

abstract class SVGNode {

    protected $parent;
    protected $x, $y, $width, $height;
    protected $styles;



    public function __construct() {
        $this->styles = array();
    }





    public function getStyle($name) {
        return $this->styles[$name];
    }

    public function setStyle($name, $value) {
        $this->styles[$name] = $value;
    }

    public function removeStyle($name) {
        unset($this->styles[$name]);
    }



    public function getComputedStyle($name) {

        $style = null;

        if (isset($this->styles[$name]))
            $style = $this->styles[$name];

        if (($style === null || $style === 'inherit') && isset($this->parent))
            return $this->parent->getComputedStyle($name);

        // 'inherit' is not what we want. Either get the real style, or
        // nothing at all.
        return $style !== 'inherit' ? $style : null;

    }





    public abstract function toXMLString();





    public abstract function draw($image, $imageWidth, $imageHeight, $scaleX, $scaleY, $offsetX = 0, $offsetY = 0);





    public function __toString() {
        return $this->toXMLString();
    }

}