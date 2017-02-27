<?php
namespace JangoBrick\SVG\Nodes\Structures;


use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'style'.
 * Has the attribute type and the css content.
 */
class SVGStyle extends SVGNode
{
    const TAG_NAME = 'style';

    private $css = '';

    /**
     * @param string $css | '' the css data rules.
     * @param string $type | 'text/css' the style type attribute.
     */
    public function __construct($css = '', $type = 'text/css')
    {
        parent::__construct();

        $this->css = $css;
        $this->setAttributeOptional('type', $type);
    }

    public static function constructFromAttributes($attr)
    {
        $cdata = trim(preg_replace('/^\s*\/\/<!\[CDATA\[([\s\S]*)\/\/\]\]>\s*\z/', '$1', $attr));

        return new static($cdata);
    }

    /**
     * @return string The style's type attribute.
     */
    public function getType()
    {
        return $this->getAttribute('type');
    }

    /**
     * @param $type
     *
     * @return $this This node instance, for call chaining.
     */
    public function setType($type)
    {
        return $this->setAttribute($type);
    }

    /**
     * @return string The style's cdata content.
     */
    public function getCss()
    {
        return $this->css;
    }

    /**
     * Sets the cdata content for the style
     *
     * @param $css The new cdata content
     *
     * @return $this The node instance for call chaining
     */
    public function setCss($css)
    {
        $this->css = $css;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param SVGRasterizer $rasterizer
     */
    public function rasterize(SVGRasterizer $rasterizer)
    {
        // Nothing to rasterize. All properties passed through container's global styles
        return;
    }
}