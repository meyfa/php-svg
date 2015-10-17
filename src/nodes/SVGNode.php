<?php

abstract class SVGNode {

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





    public abstract function toXMLString();





    public function __toString() {
        return $this->toXMLString();
    }

}