<?php

namespace JangoBrick\SVG\Nodes\Structures;

use JangoBrick\SVG\Nodes\SVGNodeContainer;

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



    public function getSerializableAttributes()
    {
        $attrs = parent::getSerializableAttributes();

        if ($this->root) {
            $attrs['xmlns'] = 'http://www.w3.org/2000/svg';
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
}
