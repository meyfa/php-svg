<?php

class SVGImage {

    private $namespaces;
    private $nodes;



    public function __construct() {
        $this->namespaces = array();
        $this->nodes = array();
    }





    public function addNode($node) {

        if (!($node instanceof SVGRect)) {
            return false;
        }

        $this->nodes[] = $node;

    }





    public function __toString() {

        $s  = '<?xml version="1.0" standalone="no"?>' . "\n";

        $s .= '<svg xmlns="http://www.w3.org/2000/svg">' . "\n";

        foreach ($this->nodes as $node) {
            $s .= '    ' . $node . "\n";
        }

        $s .= '</svg>';

        return $s;

    }

}