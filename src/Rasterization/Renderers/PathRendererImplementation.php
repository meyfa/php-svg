<?php

namespace SVG\Rasterization\Renderers;

/**
 * This class contains the algorithms for drawing the fill and stroke of flattened paths.
 * A "flattened path" is a path where the commands have already been evaluated and all curves are replaced by line
 * segment approximations. Any transforms should also be applied already.
 *
 * Since polygons are a subcategory of flattened paths, this class can draw them as well.
 */
final class PathRendererImplementation
{
    /**
     * Fill the inner region of a flattened path. This can also be used to fill a polygon by providing a single subpath
     * array containing the polygon's points.
     *
     * This is a low-level drawing operation. Any transforms etc. should already be applied to the input points.
     *
     * @param resource  $image    The image.
     * @param float[][] $subpaths The array of subpaths, where each one is an array of consecutive coordinates.
     * @param int       $color    The fill color.
     * @param string    $fillRule The fill rule ('nonzero' or 'evenodd').
     * @return void
     */
    public static function fillMultipath($image, array $subpaths, int $color, string $fillRule = 'nonzero'): void
    {
        // whether to use the evenodd rule (vs. nonzero winding)
        $evenOdd = $fillRule === 'evenodd';

        /*
         * The following algorithm is roughly adapted from FillPathImplementation.h of the SerenityOS project, commit
         * 5a2e7d30ce2f673cd84073c1e96287329fe310db. The C++ implementation is Copyright (c) 2021 Ali Mohammad Pur.
         * SerenityOS is licensed under the BSD 2-Clause license.
         */

        // Get an array of all edges contained in all of the subpaths.
        // Since a subpath can intersect with itself just as it can intersect with other subpaths,
        // there is no need to remember to which subpath an edge belongs.
        $edges = [];
        $minY = PHP_INT_MAX;
        foreach ($subpaths as $points) {
            for ($i = 0, $n = count($points); $i < $n; $i += 2) {
                $x1 = round($points[$i]);
                $y1 = round($points[$i + 1]);
                // the last vertex gets connected back to the first vertex for a complete loop
                $x2 = round($points[($i + 2) % $n]);
                $y2 = round($points[($i + 3) % $n]);
                $edge = new PathRendererEdge($x1, $y1, $x2, $y2);
                $minY = min($minY, $edge->minY);
                $edges[] = $edge;
            }
        }

        if (count($edges) < 3) {
            return;
        }

        imagesetthickness($image, 1);

        // Sort the edges by their maximum y value, descending (i.e., edges that extend further down are sorted first).
        usort($edges, ['SVG\Rasterization\Renderers\PathRendererEdge', 'compareMaxY']);
        // Now the maxY of the entire path is just the maxY of the edge sorted first.
        // Since there is no way to know which edge has the minY, we cannot do the same for that and have to compute
        // it during the loop instead.
        $maxY = $edges[0]->maxY;

        // Not all edges are relevant the entire time. This stores only the ones that extend between y coordinates
        // that include the current scanline.
        $activeEdges = [];
        // The index into $edges of the last edge that was added to $activeEdges.
        $lastActiveEdge = 0;

        // Loop over the path area from bottom to top, so that we can make good use of the sort order of $edges.
        for ($scanline = $maxY; $scanline >= $minY; --$scanline) {
            // An edge becomes irrelevant when the scanline is higher up than the edge's minY.
            $activeEdges = array_values(array_filter($activeEdges, function ($edge) use ($scanline) {
                return $edge->minY < $scanline;
            }));

            // An edge becomes relevant when its y range starts to include $scanline.
            for ($n = count($edges); $lastActiveEdge < $n; ++$lastActiveEdge) {
                $edge = $edges[$lastActiveEdge];
                // Since $edges is sorted by maxY, if this is true, there cannot be any more edges that match.
                if ($edge->maxY < $scanline) {
                    break;
                }
                if ($edge->minY < $scanline) {
                    $activeEdges[] = $edge;
                }
            }

            if (!empty($activeEdges)) {
                // Now sort the active edges from rightmost to leftmost (i.e., by x descending).
                usort($activeEdges, ['SVG\Rasterization\Renderers\PathRendererEdge', 'compareX']);

                $windingNumber = $evenOdd ? 0 : $activeEdges[0]->direction;

                // Look at entire regions between neighboring edges, since each pixel in a region will have the same
                // fill as all the others. Start with the region between the rightmost edge and the one to the left of
                // it, then go to the region left of that, etc.
                for ($i = 1, $n = count($activeEdges); $i < $n; ++$i) {
                    $prev = $activeEdges[$i - 1];
                    $curr = $activeEdges[$i];

                    if ($evenOdd ? ($windingNumber % 2 === 0) : ($windingNumber !== 0)) {
                        // This section of the scanline is inside.
                        imageline($image, round($prev->x), $scanline, round($curr->x), $scanline, $color);
                    }

                    // The original C++ code did some checking for whether a vertex was hit directly, and if so,
                    // only incremented the winding number in certain cases.
                    // I have found this to cause some problems and solve none, so I removed it.

                    $windingNumber += $evenOdd ? 1 : $curr->direction;
                }

                // Finally, since the next step will look at the edges one pixel further up, move the $x property
                // by the inverse slope to obtain the x coordinate at that new height, i.e. slide the ray intersection
                // point along the edge.
                foreach ($activeEdges as $edge) {
                    $edge->x -= $edge->inverseSlope;
                }
            }
        }
    }

    /**
     * Draw the outline (stroke) of a single subpath. To render a complete path consisting of multiple subpaths,
     * call this method repeatedly with each of the subpaths in order. The subpath is drawn as an open polyline, i.e.,
     * the start and end points are not connected.
     *
     * This is a low-level drawing operation. Any transforms etc. should already be applied to the input points.
     *
     * @param resource $image       The image.
     * @param float[]  $points      The subpath, which is an array of consecutive coordinates.
     * @param int      $color       The stroke color.
     * @param float    $strokeWidth The stroke width.
     * @return void
     */
    public static function strokeOpenSubpath($image, array $points, int $color, float $strokeWidth): void
    {
        // require at least 2 coordinate pairs to stroke a line
        if (count($points) < 4) {
            return;
        }

        imagesetthickness($image, round($strokeWidth));

        $px = round($points[0]);
        $py = round($points[1]);

        for ($i = 2, $n = count($points); $i < $n; $i += 2) {
            $x = round($points[$i]);
            $y = round($points[$i + 1]);
            imageline($image, $px, $py, $x, $y, $color);
            $px = $x;
            $py = $y;
        }
    }

    /**
     * Draw the outline (stroke) of a single subpath. To render a complete path consisting of multiple subpaths,
     * call this method repeatedly with each of the subpaths in order. The subpath is drawn as a closed polygon, i.e.,
     * the start and end points will be connected to form a loop.
     *
     * This is a low-level drawing operation. Any transforms etc. should already be applied to the input points.
     *
     * @param resource $image       The image.
     * @param float[]  $points      The subpath, which is an array of consecutive coordinates.
     * @param int      $color       The stroke color.
     * @param float    $strokeWidth The stroke width.
     * @return void
     */
    public static function strokeClosedSubpath($image, array $points, int $color, float $strokeWidth): void
    {
        // imagepolygon() requires at least 3 coordinate pairs
        if (count($points) < 6) {
            self::strokeOpenSubpath($image, $points, $color, $strokeWidth);
            return;
        }

        $roundedCoordinates = array_map('round', $points);

        imagesetthickness($image, round($strokeWidth));
        imagepolygon($image, $roundedCoordinates, count($roundedCoordinates) / 2, $color);
    }
}
