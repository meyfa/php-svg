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

    /**
     * @param string $name The tag name.
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $this->children = array();
    }

    /**
     * Adds an SVGNode instance to the end of this container's child list.
     * Does nothing if it already exists.
     *
     * @param SVGNode $node The node to add to this container's children.
     *
     * @return bool Whether the action was valid and the child list changed.
     */
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

    /**
     * Removes a child node, given either as its instance or as the index it's
     * located at, from this container.
     *
     * @param SVGNode|int $nodeOrIndex The node (or respective index) to remove.
     *
     * @return bool Whether the action was valid and the child list changed.
     */
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
        foreach ($this->children as $child) {
            $child->rasterize($rasterizer);
        }
    }
}
