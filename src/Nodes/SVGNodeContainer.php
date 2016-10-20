<?php

namespace JangoBrick\SVG\Nodes;

use JangoBrick\SVG\Rasterization\SVGRasterizer;

abstract class SVGNodeContainer extends SVGNode
{
    protected $children;

    public function __construct($name)
    {
        parent::__construct($name);

        $this->children = array();
    }

    public function addChild(SVGNode $node)
    {
        if ($node === $this) {
            return false;
        }

        if ($node->parent === $this) {
            return false;
        }

        if (isset($node->parent)) {
            $node->parent->removeChild($node);
        }

        $this->children[] = $node;
        $node->parent     = $this;

        return true;
    }

    public function removeChild($nodeOrIndex)
    {
        $index = $this->resolveChildIndex($nodeOrIndex);
        if ($index === false) {
            return false;
        }

        $node         = $this->children[$index];
        $node->parent = null;

        array_splice($this->children, $index, 1);

        return true;
    }

    private function resolveChildIndex($nodeOrIndex)
    {
        if (is_int($nodeOrIndex)) {
            return $nodeOrIndex;
        } elseif ($nodeOrIndex instanceof SVGNode) {
            return array_search($nodeOrIndex, $this->children, true);
        }

        return false;
    }

    public function countChildren()
    {
        return count($this->children);
    }

    public function getChild($index)
    {
        return $this->children[$index];
    }

    public function rasterize(SVGRasterizer $rasterizer)
    {
        foreach ($this->children as $child) {
            $child->rasterize($rasterizer);
        }
    }
}
