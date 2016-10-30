<?php

namespace JangoBrick\SVG\Rasterization;

use JangoBrick\SVG\Nodes\SVGNode;

/**
 * This class is the main entry point for the rasterization process.
 *
 * Each constructed instance represents one output image.
 * Rasterization happens by invoking `render()` with the id of a specific
 * renderer, e.g. 'ellipse' or 'polygon', which then performs the actual
 * drawing.
 * Note that renderers DO NOT correspond 1:1 to node types (e.g. there is no
 * renderer 'circle', but 'ellipse' with equal radiuses is used).
 */
class SVGRasterizer
{
    /** @var Renderers\SVGRenderer[] $renderers Map of shapes to renderers. */
    private static $renderers;
    /** @var Path\SVGPathParser The singleton path parser. */
    private static $pathParser;
    /** @var Path\SVGPathApproximator The singleton path approximator. */
    private static $pathApproximator;

    /**
     * @var int $docWidth  The original SVG document width, in pixels.
     * @var int $docHeight The original SVG document height, in pixels.
     */
    private $docWidth, $docHeight;
    /**
     * @var int $width  The output image width, in pixels.
     * @var int $height The output image height, in pixels.
     */
    private $width, $height;
    /**
     * @var float $scaleX The factor by which output is scaled on the x-axis.
     * @var float $scaleY The factor by which output is scaled on the y-axis.
     */
    private $scaleX, $scaleY;
    /** @var resource $outImage The output image as a GD resource. */
    private $outImage;

    /**
     * @param int $docWidth  The original SVG document width, in pixels.
     * @param int $docHeight The original SVG document height, in pixels.
     * @param int $width     The output image width, in pixels.
     * @param int $height    The output image height, in pixels.
     */
    public function __construct($docWidth, $docHeight, $width, $height)
    {
        $this->docWidth  = $docWidth;
        $this->docHeight = $docHeight;

        $this->width  = $width;
        $this->height = $height;

        $this->scaleX = $width / $docWidth;
        $this->scaleY = $height / $docHeight;

        $this->outImage = self::createImage($width, $height);

        self::createDependencies();
    }

    /**
     * Sets up a new truecolor GD image resource with the given dimensions.
     *
     * The returned image supports and is filled with transparency.
     *
     * @param int $width  The output image width, in pixels.
     * @param int $height The output image height, in pixels.
     *
     * @return resource The created GD image resource.
     */
    private static function createImage($width, $height)
    {
        $img = imagecreatetruecolor($width, $height);

        imagealphablending($img, true);
        imagesavealpha($img, true);

        imagefill($img, 0, 0, 0x7F000000);

        return $img;
    }



    /**
     * Makes sure the singleton static variables are all instantiated.
     *
     * This includes registering all of the standard renderers, as well as
     * preparing the path parser and the path approximator.
     *
     * @return void
     */
    private static function createDependencies()
    {
        if (isset(self::$renderers)) {
            return;
        }

        self::$renderers = array(
            'rect'      => new Renderers\SVGRectRenderer(),
            'line'      => new Renderers\SVGLineRenderer(),
            'ellipse'   => new Renderers\SVGEllipseRenderer(),
            'polygon'   => new Renderers\SVGPolygonRenderer(),
        );

        self::$pathParser       = new Path\SVGPathParser();
        self::$pathApproximator = new Path\SVGPathApproximator();
    }

    /**
     * Finds the renderer registered with the given id.
     *
     * @param string $id The id of a registered renderer instance.
     *
     * @return Renderers\SVGRenderer The requested renderer.
     * @throws \InvalidArgumentException If no such renderer exists.
     */
    private static function getRenderer($id)
    {
        if (!isset(self::$renderers[$id])) {
            throw new \InvalidArgumentException("no such renderer: ".$id);
        }
        return self::$renderers[$id];
    }

    /**
     * @return Path\SVGPathParser The path parser used by this instance.
     */
    // implementation note: although $pathParser is static, this method isn't,
    // to encourage access via passed instances (better for testing etc)
    public function getPathParser()
    {
        return self::$pathParser;
    }

    /**
     * @return Path\SVGPathApproximator The approximator used by this instance.
     */
    // implementation note: (see 'getPathParser()')
    public function getPathApproximator()
    {
        return self::$pathApproximator;
    }



    /**
     * Uses the specified renderer to draw an object, as described via the
     * params attribute, and by utilizing the provided node context.
     *
     * The node is required for access to things like the opacity as well as
     * stroke/fill attributes etc.
     *
     * @param string  $rendererId The id of the renderer to use.
     * @param mixed[] $params     An array of options to pass to the renderer.
     * @param SVGNode $context    The SVGNode that serves as drawing context.
     *
     * @return mixed Whatever the renderer returned (in most cases void).
     * @throws \InvalidArgumentException If no such renderer exists.
     */
    public function render($rendererId, array $params, SVGNode $context)
    {
        $renderer = self::getRenderer($rendererId);
        return $renderer->render($this, $params, $context);
    }



    /**
     * @return int The original SVG document width, in pixels.
     */
    public function getDocumentWidth()
    {
        return $this->docWidth;
    }

    /**
     * @return int The original SVG document height, in pixels.
     */
    public function getDocumentHeight()
    {
        return $this->docHeight;
    }



    /**
     * @return int The output image width, in pixels.
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int The output image height, in pixels.
     */
    public function getHeight()
    {
        return $this->height;
    }



    /**
     * @return float The factor by which the output is scaled on the x axis.
     */
    public function getScaleX()
    {
        return $this->scaleX;
    }

    /**
     * @return float The factor by which the output is scaled on the y axis.
     */
    public function getScaleY()
    {
        return $this->scaleY;
    }



    /**
     * @return resource The GD image resource this rasterizer is operating on.
     */
    public function getImage()
    {
        return $this->outImage;
    }
}
