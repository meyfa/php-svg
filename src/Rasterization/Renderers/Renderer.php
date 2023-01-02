<?php

namespace SVG\Rasterization\Renderers;

use SVG\Nodes\SVGNode;
use SVG\Rasterization\SVGRasterizer;

/**
 * This is the base class for all shape renderer instances.
 *
 * It contains the method `render(SVGRasterizer, array, SVGNode)` that is used
 * to invoke a drawing operation.
 * Subclasses generally require a special associative options array to be passed
 * to this render method; see their docs for a list of things to provide.
 */
abstract class Renderer
{
    /**
     * Renders the shape to the rasterizer, using the given options and node
     * context.
     *
     * The node is required for access to things like the opacity as well as
     * stroke/fill attributes etc.
     * The options array is subclass-specific; for example, ellipse renderers
     * might require the center and the radii, while polygon renderers might
     * require an array of points. For details, see the respective subclasses.
     *
     * Note that as part of the renderer contract, every option that is passed
     * in must not be offset, scaled or even validated beforehand. Such things
     * are dealt with by the renderer as needed.
     *
     * @param SVGRasterizer $rasterizer The rasterizer to render to.
     * @param array         $options    Associative array of renderer options.
     * @param SVGNode       $context    The SVGNode serving as the context.
     *
     * @return void
     */
    abstract public function render(SVGRasterizer $rasterizer, array $options, SVGNode $context): void;
}
