<?php
namespace SVG\Nodes\Structures;

/**
 * Class SVGEmbeddedFont
 * @package SVG\Nodes\Structures
 */
class SVGEmbeddedFont extends SVGFont
{
    /**
     * @param string $name
     * @param string $path
     * @param string $mimeType e. g. "application/x-font-ttf" for TrueTypeFonts
     */
    public function __construct($name, $path, $mimeType)
    {
        parent::__construct($name, $path);

        $fontContent = file_get_contents($path);
        if ($fontContent === false) {
            throw new \RuntimeException('Font file "' . $path . '" could not be read.');
        }

        $this->setCss(
            sprintf(
                "@font-face {font-family: %s;src:url('data:%s;base64,%s');}",
                $name,
                $mimeType,
                base64_encode($fontContent)
            )
        );
    }
}
