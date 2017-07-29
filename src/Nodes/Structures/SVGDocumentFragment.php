<?php

namespace SVG\Nodes\Structures;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

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
        'stroke-width'  => 1,
        'opacity'       => 1,
    );

    /** @var bool $root Whether this is the root document. */
    private $root;
    /** @var string[] $namespaces A map of custom namespaces to their URIs. */
    private $namespaces;

    /**
     * @param bool        $root       Whether this is the root document.
     * @param string|null $width      The declared width.
     * @param string|null $height     The declared height.
     * @param string[]    $namespaces A map of custom namespaces to their URIs.
     */
    public function __construct($root = false, $width = null, $height = null, array $namespaces = array())
    {
        parent::__construct();

        $this->root = (bool) $root;
        $this->namespaces = $namespaces;

        $this->setAttributeOptional('width', $width);
        $this->setAttributeOptional('height', $height);
    }

    /**
     * @return bool Whether this is the root document.
     */
    public function isRoot()
    {
        return $this->root;
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
     * Draws this node to the given rasterizer.
     *
     * @param SVGRasterizer $rasterizer The rasterizer to draw to.
     *
     * @return void
     */
    public function rasterize(SVGRasterizer $rasterizer)
    {
        // For every svg node create a new rasterizer with corresponding properties
        // for width, height, viewBox, document width and document height
        $svgWidth = $this->getWidth();
        $svgHeight = $this->getHeight();
        $svgViewBox  = $this->getScaledViewBox(
            $rasterizer->getDocumentWidth()/$svgWidth,
            $rasterizer->getDocumentHeight()/$svgHeight
        );
        $svgRasterizer = new SVGRasterizer(
            $svgWidth,
            $svgHeight,
            $svgViewBox,
            $rasterizer->getWidth(),
            $rasterizer->getHeight()
        );
        // Rasterize the svg and its children
        parent::rasterize($svgRasterizer);
        $img = $svgRasterizer->finish();
        // Copy rasterized image to parent's image
        imagecopy($rasterizer->getImage(), $img, 0, 0, 0, 0, $svgRasterizer->getWidth(), $svgRasterizer->getHeight());
        imagedestroy($img);
    }

    public function getSerializableAttributes()
    {
        $attrs = parent::getSerializableAttributes();

        if ($this->root) {
            $attrs['xmlns'] = 'http://www.w3.org/2000/svg';
            $attrs['xmlns:xlink'] = 'http://www.w3.org/1999/xlink';
            foreach ($this->namespaces as $namespace => $uri) {
                if (substr($namespace, 0, 6) !== 'xmlns:') {
                    $namespace = 'xmlns:'.$namespace;
                }
                $attrs[$namespace] = $uri;
            }
        }

        if (isset($attrs['width']) && $attrs['width'] === '100%') {
            unset($attrs['width']);
        }
        if (isset($attrs['height']) && $attrs['height'] === '100%') {
            unset($attrs['height']);
        }

        return $attrs;
    }

    /**
     * Returns a viewBox array (x, y, width, height) with dimensions scaled by
     * the given values.
     *
     * @param $scaleX The horizontal factor.
     *
     * @param $scaleY The vertical factor.
     *
     * @return float[]|null The scaled viewbox array.
     */
    private function getScaledViewBox($scaleX, $scaleY)
    {
        $viewBox = $this->getViewBox();
        if (empty($viewBox)) {
            return $viewBox;
        }
        $viewBox[2] *= $scaleX;
        $viewBox[3] *= $scaleY;

        return $viewBox;
    }
}
