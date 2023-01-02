<?php

namespace SVG\Nodes;

use SVG\Rasterization\SVGRasterizer;
use SVG\Shims\Str;

/**
 * Represents a single element inside an SVG image (in other words, an XML tag).
 * It stores hierarchy info, as well as attributes and styles.
 */
abstract class SVGNode
{
    /** @var SVGNodeContainer $parent The parent node. */
    protected $parent;
    /** @var string[] $namespaces A map of custom namespaces to their URIs. */
    private $namespaces;
    /** @var string[] $attributes This node's set of attributes. */
    protected $attributes;
    /** @var string[] $styles This node's set of explicit style declarations. */
    protected $styles;
    /** @var string $value This node's value */
    protected $value;

    public function __construct()
    {
        $this->namespaces = [];
        $this->attributes = [];
        $this->styles     = [];
        $this->value      = '';
    }

    /**
     * @return string This node's tag name (e.g. 'rect' or 'g').
     */
    public function getName(): string
    {
        return static::TAG_NAME;
    }

    /**
     * @return SVGNodeContainer|null This node's parent node, if not root.
     */
    public function getParent(): ?SVGNodeContainer
    {
        return $this->parent;
    }

    /**
     * Set the namespaces defined directly on this node.
     *
     * @param string[] $namespaces A mapping from namespace id => URI.
     */
    public function setNamespaces(array $namespaces): void
    {
        $this->namespaces = $namespaces;
    }

    /**
     * Obtains the value on this node.
     *
     * @return string The node's value
     */
    public function getValue(): string
    {
        return $this->value ?? '';
    }

    /**
     * Defines the value on this node.
     *
     * @param string|null $value The new node's value.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setValue(?string $value): SVGNode
    {
        if (!isset($value)) {
            unset($this->value);
            return $this;
        }
        $this->value = (string) $value;
        return $this;
    }

    /**
     * Obtains the style with the given name as specified on this node. The return value, if present, will never
     * contain any leading or trailing whitespace.
     *
     * @param string $name The name of the style to get.
     *
     * @return string|null The style value if specified on this node, else null.
     */
    public function getStyle(string $name): ?string
    {
        // Note: whitespace has been trimmed in the setter
        return $this->styles[$name] ?? null;
    }

    /**
     * Defines a style on this node. A value of null, the empty string, or strings containing only whitespace will
     * unset the property. Since whitespace surrounding style values is meaningless, it will be trimmed such that later
     * retrieval of the style property or computed style property will yield the value with no surrounding whitespace.
     *
     * @param string     $name  The name of the style to set.
     * @param mixed|null $value The new style value.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setStyle(string $name, $value): SVGNode
    {
        $value = Str::trim((string) $value);
        if (strlen($value) === 0) {
            unset($this->styles[$name]);
            return $this;
        }
        $this->styles[$name] = $value;
        return $this;
    }

    /**
     * Removes a style from this node's set of styles.
     *
     * @param string $name The name of the style to remove.
     *
     * @return $this This node instance, for call chaining.
     */
    public function removeStyle(string $name): SVGNode
    {
        unset($this->styles[$name]);
        return $this;
    }

    /**
     * Obtains the computed style with the given name. The 'computed style' is the one in effect; taking inheritance
     * and default styles into account.
     *
     * The return value, if present, will never contain any leading or trailing whitespace.
     *
     * @param string $name The name of the style to compute.
     *
     * @return string|null The style value if specified anywhere, else null.
     */
    public function getComputedStyle(string $name): ?string
    {
        $style = $this->getStyle($name);

        // If no immediate style then get style from container/global style rules
        if ($style === null && isset($this->parent)) {
            $containerStyles = $this->parent->getContainerStyleForNode($this);
            $style = $containerStyles[$name] ?? null;
        }

        // If still no style then get parent's style
        if (($style === null || $style === 'inherit') && isset($this->parent)) {
            return $this->parent->getComputedStyle($name);
        }

        // 'inherit' is not what we want. Either get the real style, or
        // nothing at all.
        return $style !== 'inherit' ? $style : null;
    }

    /**
     * Obtains the attribute with the given name as specified on this node.
     * For style attributes, use `getStyle($name)` instead.
     *
     * @param string $name The name of the attribute to get.
     *
     * @return string|null The attribute's value, or null.
     */
    public function getAttribute(string $name): ?string
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Defines an attribute on this node. A value of null will unset the
     * attribute. Note that the empty string is perfectly valid.
     *
     * @param string     $name  The name of the attribute to set.
     * @param mixed|null $value The new attribute value.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setAttribute(string $name, $value): SVGNode
    {
        if (!isset($value)) {
            unset($this->attributes[$name]);
            return $this;
        }
        $this->attributes[$name] = (string) $value;
        return $this;
    }

    /**
     * Removes an attribute from this node's set of attributes.
     *
     * @param string $name The name of the attribute to remove.
     *
     * @return $this This node instance, for call chaining.
     */
    public function removeAttribute(string $name): SVGNode
    {
        unset($this->attributes[$name]);
        return $this;
    }

    /**
     * Constructs a set of attributes that shall be included in generated XML.
     *
     * Subclasses MUST override this and include their own properties, if they
     * don't already use SVGNode's attribute set for storing them.
     *
     * @return string[] The attribute mapping to include in generated XML.
     */
    public function getSerializableAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Constructs a set of styles that shall be included in generated XML.
     *
     * Subclasses MAY override this to augment or limit the styles returned
     * (in the case of SVG default values, for example).
     *
     * @return string[] The style mapping to include in generated XML.
     */
    public function getSerializableStyles(): array
    {
        return $this->styles;
    }

    /**
     * Constructs a set of namespaces that shall be included in generated XML.
     *
     * @return string[] The namespace mapping to include in generated XML.
     */
    public function getSerializableNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * Constructs a regex pattern to use as the key to retrieve styles for this
     * node from its container.
     *
     * @return string|null The generated pattern.
     */
    public function getIdAndClassPattern(): ?string
    {
        $id = $this->getAttribute('id') != null ? Str::trim($this->getAttribute('id')) : '';
        $class = $this->getAttribute('class') != null  ? Str::trim($this->getAttribute('class')) : '';

        $pattern = '';
        if ($id !== '') {
            $pattern = '#' . $id . '|#' . $id;
        }
        if ($class !== '') {
            if ($pattern !== '') {
                $pattern .= '.' . $class . '|';
            }
            $pattern .= '.' . $class;
        }

        return $pattern === '' ? null : '/(' . $pattern . ')/';
    }

    /**
     * Returns the viewBox array (x, y, width, height) for the current node,
     * if one exists.
     *
     * @return float[]|null The viewbox array.
     */
    public function getViewBox(): ?array
    {
        if ($this->getAttribute('viewBox') == null) {
            return null;
        }
        $attr = Str::trim($this->getAttribute('viewBox'));
        $result = preg_split('/[\s,]+/', $attr);
        if (count($result) !== 4) {
            return null;
        }

        return array_map('floatval', $result);
    }

    /**
     * Draws this node to the given rasterizer.
     *
     * @param SVGRasterizer $rasterizer The rasterizer to draw to.
     *
     * @return void
     */
    abstract public function rasterize(SVGRasterizer $rasterizer): void;

    /**
     * Returns all descendants of this node (excluding this node) having the
     * given tag name. '*' matches all nodes.
     *
     * Example: getElementsByTagName('rect')
     * would return all <rect /> nodes that are descendants of this node.
     *
     * @param string $tagName The tag name to search for ('*' to match all).
     * @param SVGNode[] $result The array to fill. Can be omitted.
     *
     * @return SVGNode[] An array of matching elements.
     *
     * @SuppressWarnings("unused")
     */
    public function getElementsByTagName(string $tagName, array &$result = []): array
    {
        return $result;
    }

    /**
     * Returns all descendants of this node (excluding this node) having the
     * given class name (or names).
     *
     * Example 1: getElementsByClassName('foo')
     * would return all nodes whose class attribute contains the item 'foo'
     * (e.g. class="foo", class="a b foo bar", etc)
     *
     * Example 2: getElementsByClassName('foo bar')
     * or alternatively: getElementsByClassName(['foo', 'bar'])
     * would return all nodes whose class attribute contains both items
     * 'foo' and 'bar'
     * (e.g. class="a b foo qux bar", but not class="foo")
     *
     * @param string|string[] $className The class name or names to search for.
     * @param SVGNode[] $result The array to fill. Can be omitted.
     *
     * @return SVGNode[] An array of matching elements.
     *
     * @SuppressWarnings("unused")
     */
    public function getElementsByClassName($className, array &$result = []): array
    {
        return $result;
    }
}
