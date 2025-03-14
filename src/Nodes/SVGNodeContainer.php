<?php

namespace SVG\Nodes;

use SVG\Nodes\Structures\SVGStyle;
use SVG\Rasterization\SVGRasterizer;
use SVG\Rasterization\Transform\TransformParser;
use SVG\Shims\Str;
use SVG\Utilities\SVGStyleParser;

/**
 * Represents an SVG image element that contains child elements.
 */
abstract class SVGNodeContainer extends SVGNode
{
    /**
     * @var SVGNode[] $children This node's child nodes.
     */
    protected array $children;

    /**
     * @var string[] $globalStyles A 2D array mapping CSS selectors to values.
     */
    protected array $containerStyles;

    public function __construct()
    {
        parent::__construct();

        $this->containerStyles = [];
        $this->children = [];
    }

    /**
     * Inserts an SVGNode instance at the given index, or, if no index is given,
     * at the end of the child list.
     * Does nothing if the node already exists in this container.
     *
     * @param SVGNode  $node  The node to add to this container's children.
     * @param int|null $index The position to insert at (optional).
     *
     * @return $this This node instance, for call chaining.
     */
    public function addChild(SVGNode $node, ?int $index = null): SVGNodeContainer
    {
        if ($node === $this || $node->parent === $this) {
            return $this;
        }

        if (isset($node->parent)) {
            $node->parent->removeChild($node);
        }

        $index ??= count($this->children);

        // insert and set new parent
        array_splice($this->children, $index, 0, [$node]);
        $node->parent = $this;

        if ($node instanceof SVGStyle) {
            // if node is SVGStyle then add rules to container's style
            $this->addContainerStyle($node);
        }

        return $this;
    }

    /**
     * Removes a child node, given either as its instance or as the index it's
     * located at, from this container.
     *
     * @param SVGNode|int $child The node (or respective index) to remove.
     *
     * @return $this This node instance, for call chaining.
     */
    public function removeChild($child): SVGNodeContainer
    {
        $index = $this->resolveChildIndex($child);
        if ($index === false) {
            return $this;
        }

        $node         = $this->children[$index];
        $node->parent = null;

        array_splice($this->children, $index, 1);

        return $this;
    }

    /**
     * Replaces a child node with another node.
     *
     * @param SVGNode|int $child The node (or respective index) to replace.
     * @param SVGNode     $node  The replacement node.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setChild($child, SVGNode $node): SVGNodeContainer
    {
        $index = $this->resolveChildIndex($child);
        if ($index === false) {
            return $this;
        }

        $this->removeChild($index);
        $this->addChild($node, $index);

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
    public function countChildren(): int
    {
        return count($this->children);
    }

    /**
     * @param int $index The index of the child to get.
     * @return SVGNode The child node at the given index.
     */
    public function getChild(int $index): SVGNode
    {
        return $this->children[$index];
    }

    /**
     * Adds the SVGStyle element rules to container's styles.
     *
     * @param SVGStyle $styleNode The style node to add rules from.
     *
     * @return $this This node instance, for call chaining.
     */
    public function addContainerStyle(SVGStyle $styleNode): SVGNodeContainer
    {
        $newStyles = SVGStyleParser::parseCss($styleNode->getValue());
        $this->containerStyles = array_merge($this->containerStyles, $newStyles);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer): void
    {
        if ($this->getComputedStyle('display') === 'none') {
            return;
        }

        // 'visibility' can be overridden -> only applied in shape nodes.

        TransformParser::parseTransformString($this->getAttribute('transform'), $rasterizer->pushTransform());

        foreach ($this->children as $child) {
            $child->rasterize($rasterizer);
        }

        $rasterizer->popTransform();
    }

    /**
     * Returns a node's 'global' style rules.
     *
     * @param SVGNode $node The node for which we need to obtain.
     * its container style rules.
     *
     * @return string[] The style rules to be applied.
     */
    public function getContainerStyleForNode(SVGNode $node): array
    {
        $pattern = $node->getIdAndClassPattern();

        return $this->getContainerStyleByPattern($pattern);
    }

    /**
     * Returns style rules for the given node id + class pattern.
     *
     * @param string|null $pattern The node's pattern.
     *
     * @return string[] The style rules to be applied.
     */
    public function getContainerStyleByPattern(?string $pattern): array
    {
        if ($pattern === null) {
            return [];
        }

        $nodeStyles = [];
        if (!empty($this->parent)) {
            $nodeStyles = $this->parent->getContainerStyleByPattern($pattern);
        }

        $keys = $this->pregGrepStyle($pattern);
        foreach ($keys as $key) {
            $nodeStyles = array_merge($nodeStyles, $this->containerStyles[$key]);
        }

        return $nodeStyles;
    }

    /**
     * Returns the array consisting of the keys of the style rules that match
     * the given pattern.
     *
     * @param string $pattern The pattern to search for.
     *
     * @return string[] The matches array
     */
    private function pregGrepStyle(string $pattern): array
    {
        return preg_grep($pattern, array_keys($this->containerStyles));
    }

    /**
     * @inheritdoc
     */
    public function getElementsByTagName(string $tagName, array &$result = []): array
    {
        foreach ($this->children as $child) {
            if ($tagName === '*' || $child->getName() === $tagName) {
                $result[] = $child;
            }
            $child->getElementsByTagName($tagName, $result);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getElementsByClassName($className, array &$result = []): array
    {
        if (!is_array($className)) {
            $className = preg_split('/\s+/', Str::trim($className));
        }
        // shortcut if empty
        if (empty($className) || $className[0] === '') {
            return $result;
        }

        foreach ($this->children as $child) {
            $class = ' ' . $child->getAttribute('class') . ' ';
            $allMatch = true;
            foreach ($className as $cn) {
                if (strpos($class, ' ' . $cn . ' ') === false) {
                    $allMatch = false;
                    break;
                }
            }
            if ($allMatch) {
                $result[] = $child;
            }
            $child->getElementsByClassName($className, $result);
        }

        return $result;
    }
}
