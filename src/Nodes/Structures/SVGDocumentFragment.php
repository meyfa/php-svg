<?php

namespace JangoBrick\SVG\Nodes\Structures;

use JangoBrick\SVG\Nodes\SVGNodeContainer;

/**
 * Represents the SVG tag 'svg'. This is the root node for every image.
 * Has the special attributes x, y, width, height.
 */
class SVGDocumentFragment extends SVGNodeContainer
{
    /** @var mixed[] $initialStyles A map of style keys to their defaults. */
    private static $initialStyles = array(
        'fill'          => '#000000',
        'stroke'        => 'none',
        'stroke-width'  => 1,
        'opacity'       => 1,
    );

    /**
     * @var float  $x      The x position, if not root.
     * @var float  $y      The y position, if not root.
     * @var string $width  The declared width.
     * @var string $height The declared height.
     */
    protected $x, $y, $width, $height;
    /** @var bool $root Whether this is the root document. */
    private $root;
    /** @var string[] $namespaces A map of custom namespaces to their URIs. */
    private $namespaces;

    /**
     * @param bool     $root       Whether this is the root document.
     * @param string   $width      The declared width.
     * @param string   $height     The declared height.
     * @param string[] $namespaces A map of custom namespaces to their URIs.
     */
    public function __construct($root = false, $width = '100%', $height = '100%', array $namespaces = array())
    {
        parent::__construct('svg');

        $this->root = (bool) $root;
        $this->namespaces = $namespaces;

        $this->width  = $width;
        $this->height = $height;

        foreach (self::$initialStyles as $style => $value) {
            $this->setStyle($style, $value);
        }
    }

    /**
     * @inheritDoc
     * @SuppressWarnings("unused")
     */
    public static function constructFromAttributes($attrs)
    {
        return new self(false);
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
        return $this->width;
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
        $this->width = $width;
        return $this;
    }

    /**
     * @return string The declared height of this document.
     */
    public function getHeight()
    {
        return $this->height;
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
        $this->height = $height;
        return $this;
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
        } else {
            if ($this->x != 0) {
                $attrs['x'] = $this->x;
            }
            if ($this->y != 0) {
                $attrs['x'] = $this->y;
            }
        }

        if ($this->width != '100%') {
            $attrs['width'] = $this->width;
        }
        if ($this->height != '100%') {
            $attrs['height'] = $this->height;
        }

        return $attrs;
    }

    public function getSerializableStyles()
    {
        if (!$this->root) {
            return parent::getSerializableStyles();
        }

        $styles = array();
        // filter styles to not include initial/default ones
        foreach ($this->styles as $style => $value) {
            if (!isset(self::$initialStyles[$style]) || self::$initialStyles[$style] !== $value) {
                $styles[$style] = $value;
            }
        }

        return $styles;
    }
}
