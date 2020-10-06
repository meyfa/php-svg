<?php

namespace SVG\Nodes\Structures;

use RuntimeException;

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

    /**
     * @param string      $name
     * @param string      $path
     * @param bool        $embed     Embed this font file directly in the SVG?
     * @param string|null $mimeType  The MIME-Type of the font file (only needed for embedding a font into the SVG)
     */
    public function __construct($name, $path, $embed = false, $mimeType = null)
    {
        parent::__construct(
            sprintf(
                "@font-face {font-family: %s; src:url('%s');}",
                $name,
                self::resolveFontUrl($path, $embed, $mimeType)
            )
        );

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

    private static function resolveFontUrl($path, $embed, $mimeType)
    {
        if (!$embed) {
            return $path;
        }

        $data = file_get_contents($path);
        if ($data === false) {
            throw new RuntimeException('Font file "' . $path . '" could not be read.');
        }

        return sprintf('data:%s;base64,%s', $mimeType, base64_encode($data));
    }
}
