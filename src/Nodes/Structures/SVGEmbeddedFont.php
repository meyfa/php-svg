<?php
namespace SVG\Nodes\Structures;

/**
 * Class SVGEmbeddedFont
 * @package SVG\Nodes\Structures
 */
class SVGEmbeddedFont extends SVGFont
{
    public function __construct($name, $path)
    {
        parent::__construct($name, $path);

        $fontContent = file_get_contents($path);
        if ($fontContent === false) {
            throw new \RuntimeException('Font file "' . $path . '" could not be read.');
        }

        // TODO Validation via magic numbers?
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($extension === 'ttf') {
            $mimeType = 'application/x-font-ttf';
        } else {
            throw new \RuntimeException('Unknown font file extension: "' . $extension . '".');
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
