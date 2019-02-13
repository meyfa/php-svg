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
     */
    public function __construct($width, $height, $path)
    {
        parent::__construct();

        $this->setAttribute('width', $width);
        $this->setAttribute('height', $height);

        $imageContent = file_get_contents($path);
        if ($imageContent === false) {
            throw new \RuntimeException('Image file "' . $path . '" could not be read.');
        }

        // TODO Validation via magic numbers?
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($extension === 'png') {
            $mimeType = 'image/png';
        } else {
            throw new \RuntimeException('Unknown image file extension: "' . $extension . '".');
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
