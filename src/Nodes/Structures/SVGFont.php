<?php
namespace SVG\Nodes\Structures;

/**
 * Class SVGFont
 * @package SVG\Nodes\Structures
 */
class SVGFont extends SVGStyle
{
    /**
     * Font name
     *
     * @var string
     */
    private $name;

    /**
     * Absolute path to font file
     *
     * @var string
     */
    private $path;

    public function __construct($name, $path)
    {
        parent::__construct("@font-face {font-family: {$name};src:url('{$path}');}");

        $this->name = $name;
        $this->path = $path;
    }

    /**
     * Return font absolute path
     *
     * @return mixed
     */
    public function getFontPath()
    {
        return $this->path;
    }

    /**
     * Return font name
     *
     * @return string
     */
    public function getFontName()
    {
        return $this->name;
    }
}
