<?php

abstract class SVGNodeContainer extends SVGNode {

    protected $children;



    public function __construct() {
        parent::__construct();
        $this->children = array();
    }





    public function addChild($node) {

        if (!($node instanceof SVGNode))
            return false;

        if ($node === $this)
            return false;

        $this->children[] = $node;

    }



    public function countChildren() {
        return count($this->children);
    }



    public function getChild($index) {
        return $this->children[$index];
    }

}