<?php

namespace SVG\Rasterization;

use InvalidArgumentException;
use SVG\Nodes\SVGNode;
use SVG\Utilities\Units\Length;
use SVG\Utilities\Colors\Color;

/**
 * This class is the main entry point for the rasterization process.
 *
 * Each constructed instance represents one output image.
 * Rasterization happens by invoking `render()` with the id of a specific
 * renderer, e.g. 'ellipse' or 'polygon', which then performs the actual
 * drawing.
 * Note that renderers DO NOT correspond 1:1 to node types (e.g. there is no
 * renderer 'circle', but 'ellipse' with equal radiuses is used).
 *
 * @SuppressWarnings("coupling")
 */
class SVGRasterizer
{
    /** @var Renderers\Renderer[] $renderers Map of shapes to renderers. */
    private static $renderers;
    /** @var Path\PathParser The singleton path parser. */
    private static $pathParser;
    /** @var Path\PathApproximator The singleton path approximator. */
    private static $pathApproximator;

    /**
     * @var float[] The document's viewBox (x, y, w, h).
     */
    private $viewBox;

    /**
     * @var int $width  The output image width, in pixels.
     */
    private $width;
    /**
     * @var int $height The output image height, in pixels.
     */
    private $height;

    /** @var resource $outImage The output image as a GD resource. */
    private $outImage;

    // precomputed properties for getter methods, used often during render
    private $docWidth;
    private $docHeight;
    private $scaleX;
    private $scaleY;
    private $offsetX;
    private $offsetY;

    /**
     * @param string $docWidth   The original SVG document width, as a string.
     * @param string $docHeight  The original SVG document height, as a string.
     * @param float[] $viewBox   The document's viewBox.
     * @param int $width         The output image width, in pixels.
     * @param int $height        The output image height, in pixels.
     * @param string $background The background color (hex/rgb[a]/hsl[a]/...).
     */
    public function __construct($docWidth, $docHeight, $viewBox, $width, $height, $background = null)
    {
        $this->viewBox = empty($viewBox) ? null : $viewBox;

        $this->width  = $width;
        $this->height = $height;

        // precompute properties

        $this->docWidth  = Length::convert($docWidth ?: '100%', $width);
        $this->docHeight = Length::convert($docHeight ?: '100%', $height);

        $this->scaleX =  $width / (!empty($viewBox) ? $viewBox[2] : $this->docWidth);
        $this->scaleY =  $height / (!empty($viewBox) ? $viewBox[3] : $this->docHeight);

        $this->offsetX = !empty($viewBox) ? -($viewBox[0] * $this->scaleX) : 0;
        $this->offsetY = !empty($viewBox) ? -($viewBox[1] * $this->scaleY) : 0;

        // create image

        $this->outImage = self::createImage($width, $height, $background);

        self::createDependencies();
    }

    /**
     * Sets up a new truecolor GD image resource with the given dimensions.
     *
     * The returned image supports and is filled with transparency.
     *
     * @param int $width         The output image width, in pixels.
     * @param int $height        The output image height, in pixels.
     * @param string $background The background color (hex/rgb[a]/hsl[a]/...).
     *
     * @return resource The created GD image resource.
     */
    private static function createImage($width, $height, $background)
    {
        $img = imagecreatetruecolor($width, $height);

        imagealphablending($img, true);
        imagesavealpha($img, true);

        $bgRgb = 0x7F000000;
        if (!empty($background)) {
            $bgColor = Color::parse($background);

            $alpha = 127 - (int) ($bgColor[3] * 127 / 255);
            $bgRgb = ($alpha << 24) + ($bgColor[0] << 16) + ($bgColor[1] << 8) + ($bgColor[2]);
        }
        imagefill($img, 0, 0, $bgRgb);

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
            'rect'      => new Renderers\RectRenderer(),
            'line'      => new Renderers\LineRenderer(),
            'ellipse'   => new Renderers\EllipseRenderer(),
            'polygon'   => new Renderers\PolygonRenderer(),
            'image'     => new Renderers\ImageRenderer(),
            'text'      => new Renderers\TextRenderer(),
        );

        self::$pathParser       = new Path\PathParser();
        self::$pathApproximator = new Path\PathApproximator();
    }

    /**
     * Finds the renderer registered with the given id.
     *
     * @param string $id The id of a registered renderer instance.
     *
     * @return Renderers\Renderer The requested renderer.
     * @throws \InvalidArgumentException If no such renderer exists.
     */
    private static function getRenderer($id)
    {
        if (!isset(self::$renderers[$id])) {
            throw new InvalidArgumentException('no such renderer: ' . $id);
        }
        return self::$renderers[$id];
    }

    /**
     * @return Path\PathParser The path parser used by this instance.
     */
    // implementation note: although $pathParser is static, this method isn't,
    // to encourage access via passed instances (better for testing etc)
    public function getPathParser()
    {
        return self::$pathParser;
    }

    /**
     * @return Path\PathApproximator The approximator used by this instance.
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
     * @return float The original SVG document width, in pixels.
     */
    public function getDocumentWidth()
    {
        return $this->docWidth;
    }

    /**
     * @return float The original SVG document height, in pixels.
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
     * @return float The amount by which renderers must offset their drawings
     *               on the x-axis (not to be scaled).
     */
    public function getOffsetX()
    {
        return $this->offsetX;
    }

    /**
     * @return float The amount by which renderers must offset their drawings
     *               on the y-axis (not to be scaled).
     */
    public function getOffsetY()
    {
        return $this->offsetY;
    }

    /**
     * @return float[]|null The document's viewBox.
     */
    public function getViewBox()
    {
        return $this->viewBox;
    }

    /**
     * Applies final processing steps to the output image. It is then returned.
     *
     * @return resource The GD image resource this rasterizer is operating on.
     */
    public function finish()
    {
        return $this->outImage;
    }

    /**
     * @return resource The GD image resource this rasterizer is operating on.
     */
    public function getImage()
    {
        return $this->outImage;
    }
}
