<?php

namespace JangoBrick\SVG\Nodes;

use JangoBrick\SVG\Rasterization\SVGRasterizer;

/**
 * Represents an SVG image element that contains child elements.
 */
abstract class SVGNodeContainer extends SVGNode
{
    /** @var SVGNode[] $children This node's child nodes. */
    protected $children;

    public function __construct()
    {
        parent::__construct();

        $this->children = array();
    }

    /**
     * Adds an SVGNode instance to the end of this container's child list.
     * Does nothing if it already exists.
     *
     * @param SVGNode $node The node to add to this container's children.
     *
     * @return $this This node instance, for call chaining.
     */
    public function addChild(SVGNode $node)
    {
        if ($node === $this || $node->parent === $this) {
            return $this;
        }

        if (isset($node->parent)) {
            $node->parent->removeChild($node);
        }

        $this->children[] = $node;
        $node->parent     = $this;

        return $this;
    }

    /**
     * Removes a child node, given either as its instance or as the index it's
     * located at, from this container.
     *
     * @param SVGNode|int $nodeOrIndex The node (or respective index) to remove.
     *
     * @return $this This node instance, for call chaining.
     */
    public function removeChild($nodeOrIndex)
    {
        $index = $this->resolveChildIndex($nodeOrIndex);
        if ($index === false) {
            return $this;
        }

        $node         = $this->children[$index];
        $node->parent = null;

        array_splice($this->children, $index, 1);

        return $this;
    }

    /**
     * Resolves a child node to its index. If an index is given, it is returned
     * without modification.
     *
     * @param SVGNode|int $nodeOrIndex The node (or respective index).
     *
     * @return int|false The index, or false if argument invalid or not a child.
     */
    private function resolveChildIndex($nodeOrIndex)
    {
        if (is_int($nodeOrIndex)) {
            return $nodeOrIndex;
        } elseif ($nodeOrIndex instanceof SVGNode) {
            return array_search($nodeOrIndex, $this->children, true);
        }

        return false;
    }

    /**
     * @return int The amount of children in this container.
     */
    public function countChildren()
    {
        return count($this->children);
    }

    /**
     * @return SVGNode The child node at the given index.
     */
    public function getChild($index)
    {
        return $this->children[$index];
    }

    public function rasterize(SVGRasterizer $rasterizer)
    {
        if ($this->getComputedStyle('display') === 'none') {
            return;
        }

        // 'visibility' can be overridden -> only applied in shape nodes.

        foreach ($this->children as $child) {
            $child->rasterize($rasterizer);
        }
    }
}
