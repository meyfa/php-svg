<?php

namespace SVG\Nodes\Structures;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;
use SVG\Utilities\Units\Length;

/**
 * Represents the SVG tag 'svg'. This is the root node for every image.
 * Has the special attributes x, y, width, height.
 */
class SVGDocumentFragment extends SVGNodeContainer
{
    const TAG_NAME = 'svg';

    /** @var mixed[] $initialStyles A map of style keys to their defaults. */
    private static $initialStyles = array(
        'fill'          => '#000000',
        'stroke'        => 'none',
        'stroke-width'  => '1',
        'opacity'       => '1',
    );

    /**
     * @param string|null $width  The declared width.
     * @param string|null $height The declared height.
     */
    public function __construct($width = null, $height = null)
    {
        parent::__construct();

        $this->setAttribute('width', $width);
        $this->setAttribute('height', $height);
    }

    /**
     * @return bool Whether this is the root document.
     */
    public function isRoot()
    {
        return $this->getParent() === null;
    }

    /**
     * @return string The declared width of this document.
     */
    public function getWidth()
    {
        return $this->getAttribute('width');
    }

    /**
     * Declares a new width for this document.
     *
     * @param string $width The new width.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setWidth($width)
    {
        return $this->setAttribute('width', $width);
    }

    /**
     * @return string The declared height of this document.
     */
    public function getHeight()
    {
        return $this->getAttribute('height');
    }

    /**
     * Declares a new height for this document.
     *
     * @param string $height The new height.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setHeight($height)
    {
        return $this->setAttribute('height', $height);
    }

    /**
     * @inheritdoc
     */
    public function getComputedStyle($name)
    {
        // return either explicit declarations ...
        $style = parent::getComputedStyle($name);
        if (isset($style) || !isset(self::$initialStyles[$name])) {
            return $style;
        }

        // ... or the default one.
        return self::$initialStyles[$name];
    }

    /**
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer)
    {
        if ($this->isRoot()) {
            parent::rasterize($rasterizer);
            return;
        }

        // create new rasterizer for nested viewport
        $subRasterizer = new SVGRasterizer(
            $this->getWidth(),          // document width
            $this->getHeight(),         // document height
            $this->getViewBox(),        // viewBox
            Length::convert($this->getWidth() ?: '100%', $rasterizer->getWidth()),
            Length::convert($this->getHeight() ?: '100%', $rasterizer->getHeight())
        );

        // perform rasterization as usual
        parent::rasterize($subRasterizer);
        $img = $subRasterizer->finish();

        // copy nested viewport onto parent viewport
        imagecopy(
            $rasterizer->getImage(),    // destination
            $img,                       // source
            0,                          // dst_x
            0,                          // dst_y
            0,                          // srx_x
            0,                          // src_y
            $subRasterizer->getWidth(), // src_w
            $subRasterizer->getHeight() // src_h
        );
        imagedestroy($img);
    }

    /**
     * @inheritdoc
     */
    public function getSerializableAttributes()
    {
        $attrs = parent::getSerializableAttributes();

        if (isset($attrs['width']) && $attrs['width'] === '100%') {
            unset($attrs['width']);
        }
        if (isset($attrs['height']) && $attrs['height'] === '100%') {
            unset($attrs['height']);
        }

        return $attrs;
    }

    /**
     * @inheritdoc
     */
    public function getSerializableNamespaces()
    {
        if ($this->isRoot()) {
            return parent::getSerializableNamespaces() + array(
                '' => 'http://www.w3.org/2000/svg',
                'xlink' => 'http://www.w3.org/1999/xlink',
            );
        }
        return parent::getSerializableNamespaces();
    }

    /**
     * Returns the node with the given id, or null if no such node exists in the
     * document.
     *
     * @param string $id The id to search for.
     *
     * @return \SVG\Nodes\SVGNode|null The node with the given id if it exists.
     */
    public function getElementById($id)
    {
        // start with document
        $stack = array($this);

        while (!empty($stack)) {
            $elem = array_pop($stack);
            // check current node
            if ($elem->getAttribute('id') === $id) {
                return $elem;
            }
            // add children to stack (tree order traversal)
            if ($elem instanceof SVGNodeContainer) {
                for ($i = $elem->countChildren() - 1; $i >= 0; --$i) {
                    array_push($stack, $elem->getChild($i));
                }
            }
        }

        return null;
    }
}
