<?php

namespace Jangobrick\SVG\Nodes;

abstract class SVGNodeContainer extends SVGNode
{
    protected $children;

    public function __construct()
    {
        parent::__construct();
        $this->children = [];
    }

    public function addChild($node)
    {
        if (!($node instanceof SVGNode)) {
            return false;
        }

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
        if (is_int($nodeOrIndex)) {
            $index = $nodeOrIndex;
        } elseif ($nodeOrIndex instanceof SVGNode) {
            $index = array_search($nodeOrIndex, $this->children, true);
            if ($index === false) {
                return false;
            }
        } else {
            return false;
        }

        $node         = $this->children[$index];
        $node->parent = null;

        array_splice($this->children, $index, 1);

        return true;
    }

    public function countChildren()
    {
        return count($this->children);
    }

    public function getChild($index)
    {
        return $this->children[$index];
    }
}
