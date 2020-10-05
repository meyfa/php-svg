<?php

namespace SVG\Nodes\Structures;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'script'.
 */
class SVGScript extends SVGNodeContainer
{
    const TAG_NAME = 'script';

    private $content = '';

    /**
     * @param string $content The script content.
     */
    public function __construct($content = '')
    {
        parent::__construct();

        $this->content = $content;
    }

    /**
     * @inheritdoc
     */
    public static function constructFromAttributes($attr)
    {
        $cdata = trim(preg_replace('/^\s*\/\/<!\[CDATA\[([\s\S]*)\/\/\]\]>\s*\z/', '$1', $attr));

        return new static($cdata);
    }

    /**
     * @return string The script content.
     */
    public function getScript()
    {
        return $this->content;
    }

    /**
     * Sets the script content.
     *
     * @param $content string The new cdata content
     *
     * @return $this The node instance for call chaining
     */
    public function setScript($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer)
    {
        // Nothing to rasterize.
    }
}
