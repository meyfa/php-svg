<?php

namespace SVG\Rasterization\Renderers;

/**
 * A mutable data structure used by the PathRenderer during scanline fill.
 */
class PathRendererEdge
{
    /**
     * @var float The smaller of the two y values.
     */
    public $minY;

    /**
     * @var float The larger of the two y values.
     */
    public $maxY;

    /**
     * @var int The vertical winding direction of this edge, 1 if top to bottom, -1 if bottom to top.
     */
    public $direction;

    /**
     * @var float Delta x over delta y of this edge, or 0 if the edge is fully horizontal (dy === 0).
     */
    public $inverseSlope;

    /**
     * @var float Initially, the x coordinate belonging to the maxY value, but slides upwards during scanning.
     */
    public $x;

    /**
     * Construct a new edge object from the two end points. The order of points is important here,
     * for computing the edge direction.
     *
     * @param $x1 float First point X.
     * @param $y1 float First point Y.
     * @param $x2 float Second point X.
     * @param $y2 float Second point Y.
     */
    public function __construct(float $x1, float $y1, float $x2, float $y2)
    {
        $this->minY = min($y1, $y2);
        $this->maxY = max($y1, $y2);

        $this->direction = $y1 > $y2 ? -1 : 1;
        // NOTE: do not compare ($y1 === $y2) strictly, because in PHP, (4.0 === 4) is false!
        $this->inverseSlope = $y1 == $y2 ? 0.0 : ($x1 - $x2) / ($y1 - $y2);
        $this->x = $y1 > $y2 ? $x1 : $x2;
    }

    /**
     * Comparator function for sorting edges by their $maxY descending.
     *
     * @param $a self The first edge.
     * @param $b self The second edge.
     * @return int Comparison result.
     */
    public static function compareMaxY(self $a, self $b): int
    {
        if ($a->maxY < $b->maxY) {
            return 1;
        } elseif ($a->maxY > $b->maxY) {
            return -1;
        }
        return 0;
    }

    /**
     * Comparator function for sorting edges by their $x descending.
     *
     * @param $a self The first edge.
     * @param $b self The second edge.
     * @return int Comparison result.
     */
    public static function compareX(self $a, self $b): int
    {
        if ($a->x < $b->x) {
            return 1;
        } elseif ($a->x > $b->x) {
            return -1;
        }
        return 0;
    }
}
