<?php

namespace JangoBrick\SVG\Writing;

use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\Nodes\SVGNodeContainer;

class SVGWriter
{
    private $outString;

    public function __construct()
    {
        $this->outString = '<?xml version="1.0" encoding="utf-8"?>';
    }

    public function getString()
    {
        return $this->outString;
    }

    public function writeNode(SVGNode $node)
    {
        $this->outString .= '<'.$node->getName();

        $this->appendAttributes($node->getSerializableAttributes());
        $this->appendStyles($node->getSerializableStyles());

        if (!($node instanceof SVGNodeContainer)) {
            $this->outString .= ' />';
            return;
        }

        $this->outString .= '>';
        for ($i = 0, $n = $node->countChildren(); $i < $n; ++$i) {
            $this->writeNode($node->getChild($i));
        }
        $this->outString .= '</'.$node->getName().'>';
    }

    private function appendStyles(array $styles)
    {
        if (empty($styles)) {
            return;
        }

        $string = '';
        $prependSemicolon = false;
        foreach ($styles as $key => $value) {
            if ($prependSemicolon) {
                $string .= '; ';
            }
            $prependSemicolon = true;
            $string .= $key.': '.$value;
        }

        $this->appendAttribute('style', $string);
    }

    private function appendAttributes(array $attrs)
    {
        foreach ($attrs as $key => $value) {
            $this->appendAttribute($key, $value);
        }
    }

    private function appendAttribute($attrName, $attrValue)
    {
        $this->outString .= ' '.$attrName.'="'.$attrValue.'"';
    }
}
