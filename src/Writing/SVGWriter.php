<?php

namespace JangoBrick\SVG\Writing;

use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\Nodes\SVGNodeContainer;

/**
 * This class is used for composing ("writing") XML strings from nodes.
 * Every instance corresponds to one output string.
 */
class SVGWriter
{
    /** @var string $outString The XML output string being written */
    private $outString;

    public function __construct()
    {
        $this->outString = '<?xml version="1.0" encoding="utf-8"?>';
    }

    /**
     * @return string The XML output string up until the point currenly written.
     */
    public function getString()
    {
        return $this->outString;
    }

    /**
     * Converts the given node into its XML representation and appends that to
     * this writer's output string.
     *
     * The generated string contains the attributes defined via
     * SVGNode::getSerializableAttributes() and the styles defined via
     * SVGNode::getSerializableStyles().
     * Container nodes (<g></g>) and self-closing tags (<rect />) are
     * distinguished correctly.
     *
     * @param SVGNode $node The node to write.
     *
     * @return void
     */
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

    /**
     * Converts the given styles into a CSS string, then appends a 'style'
     * attribute with the value set to that string to this writer's output.
     *
     * @param string[] $styles An associative array of styles for the attribute.
     *
     * @return void
     */
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

    /**
     * Appends all attributes defined in the given associative array to this
     * writer's output.
     *
     * @param string[] $attrs An associative array of attribute strings.
     *
     * @return void
     */
    private function appendAttributes(array $attrs)
    {
        foreach ($attrs as $key => $value) {
            $this->appendAttribute($key, $value);
        }
    }

    /**
     * Appends a single attribute given by key and value to this writer's
     * output.
     *
     * @param string $attrName  The attribute name.
     * @param string $attrValue The attribute value.
     *
     * @return void
     */
    private function appendAttribute($attrName, $attrValue)
    {
        $this->outString .= ' '.$attrName.'="'.$attrValue.'"';
    }
}
