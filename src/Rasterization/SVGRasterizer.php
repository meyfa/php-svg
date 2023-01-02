<?php

namespace SVG\Rasterization;

use InvalidArgumentException;
use RuntimeException;
use SVG\Fonts\FontRegistry;
use SVG\Nodes\SVGNode;
use SVG\Rasterization\Renderers\Renderer;
use SVG\Rasterization\Transform\Transform;
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
 * renderer 'circle', but 'ellipse' with equal radii is used).
 *
 * @SuppressWarnings("coupling")
 */
class SVGRasterizer
{
    /** @var Renderers\Renderer[] $renderers Map of shapes to renderers. */
    private static $renderers;

    private $fontRegistry;

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
    private $diagonalScale;

    private $transformStack;

    /**
     * @param string|null $docWidth   The original SVG document width, as a string.
     * @param string|null $docHeight  The original SVG document height, as a string.
     * @param float[]|null $viewBox   The document's viewBox.
     * @param int $width              The output image width, in pixels.
     * @param int $height             The output image height, in pixels.
     * @param string|null $background The background color (hex/rgb[a]/hsl[a]/...).
     */
    public function __construct(
        ?string $docWidth,
        ?string $docHeight,
        ?array $viewBox,
        int $width,
        int $height,
        ?string $background = null
    ) {
        $this->viewBox = empty($viewBox) ? null : $viewBox;

        $this->width  = $width;
        $this->height = $height;

        // precompute properties

        $this->docWidth  = Length::convert($docWidth ?: '100%', $width);
        $this->docHeight = Length::convert($docHeight ?: '100%', $height);

        $scaleX =  $width / (!empty($viewBox) ? $viewBox[2] : $this->docWidth ?? 0);
        $scaleY =  $height / (!empty($viewBox) ? $viewBox[3] : $this->docHeight ?? 0);
        $this->diagonalScale = hypot($scaleX, $scaleY) / M_SQRT2;

        $offsetX = !empty($viewBox) ? -($viewBox[0] * $scaleX) : 0;
        $offsetY = !empty($viewBox) ? -($viewBox[1] * $scaleY) : 0;

        // the transform stack starts out with a simple viewport transform
        $transform = Transform::identity();
        $transform->translate($offsetX, $offsetY);
        $transform->scale($scaleX, $scaleY);
        $this->transformStack = [$transform];

        // create image

        $this->outImage = self::createImage($width, $height, $background);

        self::createDependencies();
    }

    /**
     * Sets up a new truecolor GD image resource with the given dimensions.
     *
     * The returned image supports and is filled with transparency.
     *
     * @param int $width              The output image width, in pixels.
     * @param int $height             The output image height, in pixels.
     * @param string|null $background The background color (hex/rgb[a]/hsl[a]/...).
     *
     * @return resource The created GD image resource.
     */
    private static function createImage(int $width, int $height, ?string $background)
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
    private static function createDependencies(): void
    {
        if (isset(self::$renderers)) {
            return;
        }

        self::$renderers = [
            'rect'      => new Renderers\RectRenderer(),
            'line'      => new Renderers\LineRenderer(),
            'ellipse'   => new Renderers\EllipseRenderer(),
            'polygon'   => new Renderers\PolygonRenderer(),
            'path'      => new Renderers\PathRenderer(),
            'image'     => new Renderers\ImageRenderer(),
            'text'      => new Renderers\TextRenderer(),
        ];
    }

    /**
     * Finds the renderer registered with the given id.
     *
     * @param string $id The id of a registered renderer instance.
     *
     * @return Renderer The requested renderer.
     * @throws InvalidArgumentException If no such renderer exists.
     */
    private static function getRenderer(string $id): Renderer
    {
        if (!isset(self::$renderers[$id])) {
            throw new InvalidArgumentException('no such renderer: ' . $id);
        }
        return self::$renderers[$id];
    }

    public function setFontRegistry(FontRegistry $fontRegistry): void
    {
        $this->fontRegistry = $fontRegistry;
    }

    public function getFontRegistry(): ?FontRegistry
    {
        return $this->fontRegistry;
    }

    /**
     * Uses the specified renderer to draw an object, as described via the
     * params attribute, and by utilizing the provided node context.
     *
     * The node is required for access to things like the opacity as well as
     * stroke/fill attributes etc.
     *
     * @param string  $rendererId The id of the renderer to use.
     * @param array $params       An array of options to pass to the renderer.
     * @param SVGNode $context    The SVGNode that serves as drawing context.
     *
     * @return void
     *
     * @throws InvalidArgumentException If no such renderer exists.
     */
    public function render(string $rendererId, array $params, SVGNode $context): void
    {
        $renderer = self::getRenderer($rendererId);
        $renderer->render($this, $params, $context);
    }

    /**
     * @return float|null The original SVG document width, in pixels.
     */
    public function getDocumentWidth(): ?float
    {
        return $this->docWidth;
    }

    /**
     * @return float|null The original SVG document height, in pixels.
     */
    public function getDocumentHeight(): ?float
    {
        return $this->docHeight;
    }

    /**
     * Obtain the normalized diagonal of the SVG viewport. The normalized diagonal is to be used as the reference size
     * for percentages that don't strictly refer to the horizontal or vertical axis. Examples of such values are
     * a circle's radius attribute or the stroke-width.
     *
     * This is computed by the formula <code>hypot(documentWidth, documentHeight)/sqrt(2)</code>.
     *
     * @return float The normalized diagonal length.
     */
    public function getNormalizedDiagonal(): float
    {
        // https://svgwg.org/svg2-draft/coords.html#Units

        // For any other length value expressed as a percentage of the SVG viewport, the percentage must be calculated
        // as a percentage of the normalized diagonal of the ‘viewBox’ applied to that viewport. If no ‘viewBox’ is
        // specified, then the normalized diagonal of the SVG viewport must be used. The normalized diagonal length must
        // be calculated with sqrt((width)**2 + (height)**2)/sqrt(2).

        return hypot($this->docWidth ?? 0, $this->docHeight ?? 0) / M_SQRT2;
    }

    /**
     * @return int The output image width, in pixels.
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return int The output image height, in pixels.
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * Determine the normalized diagonal scaling factor. This is the factor that should be used when scaling percentages
     * for properties that are not strictly horizontal or strictly vertical, such as stroke-width.
     *
     * @return float The scaling factor of the view diagonal.
     */
    public function getDiagonalScale(): float
    {
        return $this->diagonalScale;
    }

    /**
     * @return float[]|null The document's viewBox.
     */
    public function getViewBox(): ?array
    {
        return $this->viewBox;
    }

    /**
     * Obtain a Transform object from userspace coordinates into output image coordinates.
     *
     * This will NOT create a copy, so mutating the returned object is unsafe (= might affect later code in unexpected
     * ways). Instead, perform a call to <code>pushTransform()</code> and mutate the return value of that. Then, when
     * done using the changed transform, call <code>popTransform()</code> to revert back to the previous state.
     *
     * @return Transform The created transform.
     */
    public function getCurrentTransform(): Transform
    {
        return $this->transformStack[count($this->transformStack) - 1];
    }

    /**
     * Create a copy of the current transform and push it onto the transform stack, so that it becomes the new
     * current transform. The copied transform is then returned and can be manipulated. When done rendering with this
     * mutated transform, call <code>popTransform()</code> to revert back to the previous transform.
     *
     * @return Transform The copy of the current transform, ready to have operations appended to it.
     */
    public function pushTransform(): Transform
    {
        $nextTransform = clone $this->transformStack[count($this->transformStack) - 1];
        $this->transformStack[] = $nextTransform;
        return $nextTransform;
    }

    /**
     * Revert back to the previous transform, by removing the last transform that was pushed via
     * <code>pushTransform()</code>. There must be a matching call to <code>popTransform</code> for every call to
     * <code>pushTransform()</code>. Popping a transform when no pushed transform remains is an error.
     *
     * @return void
     * @throws RuntimeException If trying to pop a transform but the stack contains only the initial transform.
     */
    public function popTransform(): void
    {
        if (count($this->transformStack) <= 1) {
            throw new RuntimeException('popTransform() called with no transform on the stack!');
        }
        array_pop($this->transformStack);
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
