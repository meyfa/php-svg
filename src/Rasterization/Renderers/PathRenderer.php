<?php

namespace SVG\Rasterization\Renderers;

use SVG\Rasterization\Path\PathApproximator;
use SVG\Rasterization\Transform\Transform;

/**
 * This renderer can draw arbitrary paths. It expects the paths to be given as the command format as returned by
 * PathParser. That format consists of an outer array containing one entry per command, with each such entry comprised
 * of an 'id' property and an 'args' property, which is itself an array of numbers.
 *
 * During render, these commands will be approximated into polygonal subpaths. Since these subpaths can potentially
 * overlap, a special rendering algorithm distinct from the polygonal renderer is implemented that respects
 * fill rules (evenodd, nonzero winding).
 *
 * Options:
 * - array[] commands: The path commands, each containing an id string and an args array.
 */
class PathRenderer extends MultiPassRenderer
{
    /**
     * @inheritdoc
     */
    protected function prepareRenderParams(array $options, Transform $transform)
    {
        $approximator = new PathApproximator($transform);
        $approximator->approximate($options['commands']);

        $segments = array();
        foreach ($approximator->getSubpaths() as $subpath) {
            $points = array();
            foreach ($subpath as $point) {
                $points[] = $point[0];
                $points[] = $point[1];
            }
            $segments[] = $points;
        }

        return array(
            'segments'  => $segments,
            'fill-rule' => $options['fill-rule'],
        );
    }

    /**
     * @inheritdoc
     */
    protected function renderFill($image, array $params, $color)
    {
        imagesetthickness($image, 1);

        // whether to use the evenodd rule (vs. nonzero winding)
        $evenOdd = $params['fill-rule'] === 'evenodd';

        /*
         * The following algorithm is roughly adapted from FillPathImplementation.h of the SerenityOS project, commit
         * 5a2e7d30ce2f673cd84073c1e96287329fe310db. The C++ implementation is Copyright (c) 2021 Ali Mohammad Pur.
         * SerenityOS is licensed under the BSD 2-Clause license.
         */

        // Get an array of all edges contained in all of the subpaths.
        // Since a subpath can intersect with itself just as it can intersect with other subpaths,
        // there is no need to remember to which subpath an edge belongs.
        $edges = array();
        $minY = $params['segments'][0][1];
        foreach ($params['segments'] as $points) {
            for ($i = 0, $n = count($points); $i < $n; $i += 2) {
                $x1 = $points[$i];
                $y1 = $points[$i + 1];
                // the last vertex gets connected back to the first vertex for a complete loop
                $x2 = $points[($i + 2) % $n];
                $y2 = $points[($i + 3) % $n];
                $edge = new PathRendererEdge($x1, $y1, $x2, $y2);
                $minY = min($minY, $edge->minY);
                $edges[] = $edge;
            }
        }

        if (count($edges) < 3) {
            return;
        }

        // Sort the edges by their maximum y value, descending (i.e., edges that extend further down are sorted first).
        usort($edges, array('SVG\Rasterization\Renderers\PathRendererEdge', 'compareMaxY'));
        // Now the maxY of the entire path is just the maxY of the edge sorted first.
        // Since there is no way to know which edge has the minY, we cannot do the same for that and have to compute
        // it during the loop instead.
        $maxY = $edges[0]->maxY;

        // Not all edges are relevant the entire time. This stores only the ones that extend between y coordinates
        // that include the current scanline.
        $activeEdges = array();
        // The index into $edges of the last edge that was added to $activeEdges.
        $lastActiveEdge = 0;

        // Loop over the path area from bottom to top, so that we can make good use of the sort order of $edges.
        for ($scanline = $maxY; $scanline >= $minY; --$scanline) {
            // An edge becomes irrelevant when the scanline is higher up than the edge's minY.
            $activeEdges = array_values(array_filter($activeEdges, function ($edge) use ($scanline) {
                return $scanline > $edge->minY;
            }));

            // An edge becomes relevant when its y range starts to include $scanline.
            for ($i = $lastActiveEdge, $n = count($edges); $i < $n; ++$i) {
                $edge = $edges[$i];
                // Since $edges is sorted by maxY, if this is true, there cannot be any more edges that match.
                if ($edge->maxY < $scanline) {
                    break;
                }
                if ($edge->minY < $scanline) {
                    $activeEdges[] = $edge;
                }
                ++$lastActiveEdge;
            }

            if (!empty($activeEdges)) {
                // Now sort the active edges from rightmost to leftmost (i.e., by x descending).
                usort($activeEdges, array('SVG\Rasterization\Renderers\PathRendererEdge', 'compareX'));

                $windingNumber = $evenOdd ? 0 : $activeEdges[0]->direction;

                // Look at entire regions between neighboring edges, since each pixel in a region will have the same
                // fill as all the others. Start with the region between the rightmost edge and the one to the left of
                // it, then go to the region left of that, etc.
                for ($i = 1, $n = count($activeEdges); $i < $n; ++$i) {
                    $prev = $activeEdges[$i - 1];
                    $curr = $activeEdges[$i];

                    if ($evenOdd ? ($windingNumber % 2 === 0) : ($windingNumber !== 0)) {
                        // This section of the scanline is inside.
                        imageline($image, $prev->x, $scanline, $curr->x, $scanline, $color);
                    }

                    // The original C++ code did some checking for whether a vertex was hit directly, and if so,
                    // only incremented the winding number in certain cases.
                    // I have found this to cause some problems and solve none, so I removed it.

                    // There are some weird cases when the ray hits a vertex directly, which we have to account for.
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
     * @inheritdoc
     */
    protected function renderStroke($image, array $params, $color, $strokeWidth)
    {
        imagesetthickness($image, round($strokeWidth));

        foreach ($params['segments'] as $points) {
            $this->renderStrokeOpen($image, $points, $color);
        }
    }

    private function renderStrokeOpen($image, array $points, $color)
    {
        // This is just the same as for polylines.

        $px = $points[0];
        $py = $points[1];

        for ($i = 2, $n = count($points); $i < $n; $i += 2) {
            $x = $points[$i];
            $y = $points[$i + 1];
            imageline($image, $px, $py, $x, $y, $color);
            $px = $x;
            $py = $y;
        }
    }
}
