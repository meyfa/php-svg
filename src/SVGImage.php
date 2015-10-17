<?php

class SVGImage {

    private $namespaces;
    private $document;



    public function __construct() {
        $this->namespaces = array();
        $this->document = new SVGDocumentFragment(true); // root doc
    }





    public function getDocument() {
        return $this->document;
    }





    public function __toString() {

        $s  = '<?xml version="1.0" encoding="utf-8"?>';
        $s .= $this->document;

        return $s;

    }

}