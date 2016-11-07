<?php

namespace JangoBrick\SVG\Nodes;

use JangoBrick\SVG\Rasterization\SVGRasterizer;

/**
 * Represents a single element inside an SVG image (in other words, an XML tag).
 * It stores hierarchy info, as well as attributes and styles.
 */
abstract class SVGNode
{
    /** @var SVGNodeContainer $parent The parent node. */
    protected $parent;
    /** @var string[] $styles This node's set of explicit style declarations. */
    protected $styles;
    /** @var string[] $attributes This node's set of attributes. */
    protected $attributes;

    public function __construct()
    {
        $this->styles     = array();
        $this->attributes = array();
    }

    /**
     * Factory function for this class, which accepts an associative array of
     * strings instead of parameters in the correct order (like `__construct`).
     *
     * By default, simply invokes the constructor with no arguments. Subclasses
     * may choose to override this if they require special behavior.
     *
     * @param string[] $attrs The attribute array (or array-like object; e.g. \SimpleXMLElement).
     *
     * @return static A new instance of the class this was called on.
     *
     * @SuppressWarnings("unused")
     */
    public static function constructFromAttributes($attrs)
    {
        return new static();
    }



    /**
     * @return string This node's tag name (e.g. 'rect' or 'g').
     */
    public function getName()
    {
        return static::TAG_NAME;
    }

    /**
     * @return SVGNodeContainer|null This node's parent node, if not root.
     */
    public function getParent()
    {
        return $this->parent;
    }



    /**
     * Obtains the style with the given name as specified on this node.
     *
     * @param string $name The name of the style to get.
     *
     * @return string|null The style value if specified on this node, else null.
     */
    public function getStyle($name)
    {
        return isset($this->styles[$name]) ? $this->styles[$name] : null;
    }

    /**
     * Defines a style on this node.
     *
     * @param string $name  The name of the style to set.
     * @param string $value The new style value.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setStyle($name, $value)
    {
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
    public function removeStyle($name)
    {
        unset($this->styles[$name]);
        return $this;
    }

    /**
     * Obtains the computed style with the given name. The 'computed style' is
     * the one in effect; taking inheritance and default styles into account.
     *
     * @param string $name The name of the style to compute.
     *
     * @return string|null The style value if specified anywhere, else null.
     */
    public function getComputedStyle($name)
    {
        $style = null;

        if (isset($this->styles[$name])) {
            $style = $this->styles[$name];
        }

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
    public function getAttribute($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    /**
     * Defines an attribute on this node.
     *
     * @param string $name  The name of the attribute to set.
     * @param string $value The new attribute value.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Defines an attribute, if and only if a non-null value is given; otherwise
     * behaves like `setAttribute(...)`.
     *
     * This is useful for initializing attributes in constructors.
     *
     * @param string      $name  The name of the attribute to set.
     * @param string|null $value The new attribute value.
     *
     * @return $this This node instance, for call chaining.
     */
    protected function setAttributeOptional($name, $value = null)
    {
        if (!isset($value)) {
            return;
        }
        return $this->setAttribute($name, $value);
    }

    /**
     * Removes an attribute from this node's set of attributes.
     *
     * @param string $name The name of the attribute to remove.
     *
     * @return $this This node instance, for call chaining.
     */
    public function removeAttribute($name)
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
     * @return string[] The set of attributes to include in generated XML.
     */
    public function getSerializableAttributes()
    {
        return $this->attributes;
    }

    /**
     * Constructs a set of styles that shall be included in generated XML.
     *
     * Subclasses MAY override this to augment or limit the styles returned
     * (in the case of SVG default values, for example).
     *
     * @return string[] The set of styles to include in generated XML.
     */
    public function getSerializableStyles()
    {
        return $this->styles;
    }



    /**
     * Draws this node to the given rasterizer.
     *
     * @param SVGRasterizer $rasterizer The rasterizer to draw to.
     *
     * @return void
     */
    abstract public function rasterize(SVGRasterizer $rasterizer);
}
