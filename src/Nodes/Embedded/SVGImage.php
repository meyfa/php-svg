<?php

namespace SVG\Nodes\Embedded;

use RuntimeException;
use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;
use SVG\Rasterization\Transform\TransformParser;
use SVG\Utilities\Units\Length;

/**
 * Represents the SVG tag 'image'.
 * Has the special attributes xlink:href, x, y, width, height.
 */
class SVGImage extends SVGNodeContainer
{
    const TAG_NAME = 'image';

    /**
     * @param string|null $href   The image path, URL or URI.
     * @param mixed $x      The x coordinate of the upper left corner.
     * @param mixed $y      The y coordinate of the upper left corner.
     * @param mixed $width  The width.
     * @param mixed $height The height.
     */
    public function __construct(?string $href = null, $x = null, $y = null, $width = null, $height = null)
    {
        parent::__construct();

        $this->setAttribute('xlink:href', $href);
        $this->setAttribute('x', $x);
        $this->setAttribute('y', $y);
        $this->setAttribute('width', $width);
        $this->setAttribute('height', $height);
    }

    /**
     * Creates a new SVGImage directly from file
     *
     * @param string     $path
     * @param string     $mimeType
     * @param mixed $x
     * @param mixed $y
     * @param mixed $width
     * @param mixed $height
     *
     * @return self
     */
    public static function fromFile(
        string $path,
        string $mimeType,
        $x = null,
        $y = null,
        $width = null,
        $height = null
    ): SVGImage {
        $imageContent = file_get_contents($path);
        if ($imageContent === false) {
            throw new RuntimeException('Image file "' . $path . '" could not be read.');
        }

        return self::fromString(
            $imageContent,
            $mimeType,
            $x,
            $y,
            $width,
            $height
        );
    }

    /**
     * Creates a new SVGImage directly from a raw binary image string
     *
     * @param string     $imageContent
     * @param string     $mimeType
     * @param mixed $x
     * @param mixed $y
     * @param mixed $width
     * @param mixed $height
     *
     * @return self
     */
    public static function fromString(
        string $imageContent,
        string $mimeType,
        $x = null,
        $y = null,
        $width = null,
        $height = null
    ): SVGImage {
        return new self(
            sprintf(
                'data:%s;base64,%s',
                $mimeType,
                base64_encode($imageContent)
            ),
            $x,
            $y,
            $width,
            $height
        );
    }

    /**
     * @return string|null The image path, URL or URI.
     */
    public function getHref(): ?string
    {
        return $this->getAttribute('xlink:href') ?: $this->getAttribute('href');
    }

    /**
     * Sets this image's path, URL or URI.
     *
     * @param string|null $href The new image hyper reference.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setHref(?string $href): SVGImage
    {
        return $this->setAttribute('xlink:href', $href);
    }

    /**
     * @return string|null The x coordinate of the upper left corner.
     */
    public function getX(): ?string
    {
        return $this->getAttribute('x');
    }

    /**
     * Sets the x coordinate of the upper left corner.
     *
     * @param mixed $x The new coordinate.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setX($x): SVGImage
    {
        return $this->setAttribute('x', $x);
    }

    /**
     * @return string|null The y coordinate of the upper left corner.
     */
    public function getY(): ?string
    {
        return $this->getAttribute('y');
    }

    /**
     * Sets the y coordinate of the upper left corner.
     *
     * @param mixed $y The new coordinate.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setY($y): SVGImage
    {
        return $this->setAttribute('y', $y);
    }

    /**
     * @return string|null The width.
     */
    public function getWidth(): ?string
    {
        return $this->getAttribute('width');
    }

    /**
     * @param mixed $width The new width.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setWidth($width): SVGImage
    {
        return $this->setAttribute('width', $width);
    }

    /**
     * @return string|null The height.
     */
    public function getHeight(): ?string
    {
        return $this->getAttribute('height');
    }

    /**
     * @param mixed $height The new height.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setHeight($height): SVGImage
    {
        return $this->setAttribute('height', $height);
    }

    /**
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer): void
    {
        if ($this->getComputedStyle('display') === 'none') {
            return;
        }

        $visibility = $this->getComputedStyle('visibility');
        if ($visibility === 'hidden' || $visibility === 'collapse') {
            return;
        }

        TransformParser::parseTransformString($this->getAttribute('transform'), $rasterizer->pushTransform());

        $rasterizer->render('image', [
            'href'      => $this->getHref(),
            'x'         => Length::convert($this->getX(), $rasterizer->getDocumentWidth()),
            'y'         => Length::convert($this->getY(), $rasterizer->getDocumentHeight()),
            'width'     => Length::convert($this->getWidth(), $rasterizer->getDocumentWidth()),
            'height'    => Length::convert($this->getHeight(), $rasterizer->getDocumentHeight()),
        ], $this);

        $rasterizer->popTransform();
    }
}
