<?php

namespace SVG\Fonts;

/**
 * Abstract base class for font files.
 */
abstract class FontFile
{
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return string The path of the font file.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    abstract public function getFamily(): string;

    abstract public function getWeight(): float;

    abstract public function isItalic(): bool;

    abstract public function isMonospace(): bool;
}
