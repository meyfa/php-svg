<?php
namespace SVG\Nodes\Structures;

use SVG\Nodes\SVGNode;
use SVG\Rasterization\SVGRasterizer;

/**
 * Class SVGEmbeddedImage
 * @package SVG\Nodes\Structures
 */
class SVGEmbeddedImage extends SVGNode
{
    const TAG_NAME = 'image';

    /**
     * @param int    $width
     * @param int    $height
     * @param string $path
     * @param string $mimeType e. g. "image/png" for PNG Files
     */
    public function __construct($width, $height, $path, $mimeType)
    {
        parent::__construct();

        $this->setAttribute('width', $width);
        $this->setAttribute('height', $height);

        $imageContent = file_get_contents($path);
        if ($imageContent === false) {
            throw new \RuntimeException('Image file "' . $path . '" could not be read.');
        }

        $this->setAttribute(
            'xlink:href',
            sprintf(
                'data:%s;base64,%s',
                $mimeType,
                base64_encode($imageContent)
            )
        );
    }

    public function rasterize(SVGRasterizer $rasterizer)
    {
        // TODO: Implement rasterize() method.
    }
}
