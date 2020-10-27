<?php

namespace SVG\Nodes\Shapes;

use SVG\Nodes\SVGNodeContainer;

/**
 * This is the base class for polygons and polylines.
 * Offers methods for manipulating the list of points.
 */
abstract class SVGPolygonalShape extends SVGNodeContainer
{
    /**
     * @param array[] $points Array of points (float 2-tuples).
     */
    public function __construct(array $points = null)
    {
        parent::__construct();

        if (isset($points)) {
            $this->setAttribute('points', self::joinPoints($points));
        }
    }

    /**
     * Appends a new point to the end of this shape. The point can be given
     * either as a 2-tuple (1 param) or as separate x and y (2 params).
     *
     * @param float|float[] $a The point as an array, or its x coordinate.
     * @param float|null    $b The point's y coordinate, if not given as array.
     *
     * @return $this This node instance, for call chaining.
     */
    public function addPoint($a, $b = null)
    {
        if (is_array($a)) {
            list($a, $b) = $a;
        }

        $pointsAttribute = $this->getAttribute('points');
        if (!isset($pointsAttribute)) {
            $this->setAttribute('points', $a . ',' . $b);
            return;
        }
        $this->setAttribute('points', trim($pointsAttribute . ' ' . $a . ',' . $b));

        return $this;
    }

    /**
     * Removes the point at the given index from this shape.
     *
     * @param int $index The index of the point to remove.
     *
     * @return $this This node instance, for call chaining.
     */
    public function removePoint($index)
    {
        $points = $this->getPoints();
        array_splice($points, $index, 1);
        $this->setAttribute('points', self::joinPoints($points));
        return $this;
    }

    /**
     * @return int The number of points in this shape.
     */
    public function countPoints()
    {
        return count($this->getPoints());
    }

    /**
     * @return array[] All points in this shape (array of float 2-tuples).
     */
    public function getPoints()
    {
        $pointsAttribute = $this->getAttribute('points');
        if (!isset($pointsAttribute)) {
            return array();
        }
        return self::splitPoints($pointsAttribute);
    }

    /**
     * @param int $index The index of the point to get.
     *
     * @return float[] The point at the given index (0 => x, 1 => y).
     */
    public function getPoint($index)
    {
        $points = $this->getPoints();
        return $points[$index];
    }

    /**
     * Replaces the point at the given index with a different one.
     *
     * @param int     $index The index of the point to set.
     * @param float[] $point The new point.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setPoint($index, $point)
    {
        $points = $this->getPoints();
        $points[$index] = $point;
        $this->setAttribute('points', self::joinPoints($points));
        return $this;
    }

    private static function splitPoints($pointsString)
    {
        $pointsArray = array();

        $coords = preg_split('/[\s,]+/', trim($pointsString));
        for ($i = 0, $n = count($coords); $i + 1 < $n; $i += 2) {
            $pointsArray[] = array(
                (float) $coords[$i],
                (float) $coords[$i + 1],
            );
        }

        return $pointsArray;
    }

    private static function joinPoints(array $pointsArray)
    {
        $pointsString = '';

        for ($i = 0, $n = count($pointsArray); $i < $n; ++$i) {
            $point = $pointsArray[$i];
            if ($i > 0) {
                $pointsString .= ' ';
            }
            $pointsString .= $point[0] . ',' . $point[1];
        }

        return $pointsString;
    }
}
