<?php

namespace SVG\Nodes\Shapes;

use SVG\Nodes\SVGNodeContainer;
use SVG\Shims\Str;

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
    public function addPoint($a, $b = null): SVGPolygonalShape
    {
        if (is_array($a)) {
            list($a, $b) = $a;
        }

        $pointsAttribute = $this->getAttribute('points') ?: '';
        $this->setAttribute('points', Str::trim($pointsAttribute . ' ' . $a . ',' . $b));

        return $this;
    }

    /**
     * Removes the point at the given index from this shape.
     *
     * @param int $index The index of the point to remove.
     *
     * @return $this This node instance, for call chaining.
     */
    public function removePoint(int $index): SVGPolygonalShape
    {
        $coords = self::splitCoordinates($this->getAttribute('points') ?: '');
        array_splice($coords, $index * 2, 2);
        $this->setAttribute('points', self::joinCoordinates($coords));

        return $this;
    }

    /**
     * @return int The number of points in this shape.
     */
    public function countPoints(): int
    {
        $pointsAttribute = $this->getAttribute('points');
        if (isset($pointsAttribute)) {
            $coords = self::splitCoordinates($pointsAttribute);
            return (int) (count($coords) / 2);
        }
        return 0;
    }

    /**
     * @return array[] All points in this shape (array of float 2-tuples).
     */
    public function getPoints(): array
    {
        $pointsAttribute = $this->getAttribute('points');
        if (isset($pointsAttribute)) {
            return self::splitPoints($pointsAttribute);
        }
        return [];
    }

    /**
     * @param int $index The index of the point to get.
     *
     * @return float[] The point at the given index (0 => x, 1 => y).
     */
    public function getPoint(int $index): array
    {
        $coords = self::splitCoordinates($this->getAttribute('points') ?: '');
        return [
            (float) $coords[$index * 2],
            (float) $coords[$index * 2 + 1],
        ];
    }

    /**
     * Replaces the point at the given index with a different one.
     *
     * @param int     $index The index of the point to set.
     * @param float[] $point The new point.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setPoint(int $index, array $point): SVGPolygonalShape
    {
        $coords = self::splitCoordinates($this->getAttribute('points') ?: '');
        $coords[$index * 2] = $point[0];
        $coords[$index * 2 + 1] = $point[1];
        $this->setAttribute('points', self::joinCoordinates($coords));

        return $this;
    }

    private static function splitCoordinates(?string $pointsString): array
    {
        return preg_split('/[\s,]+/', Str::trim($pointsString));
    }

    private static function joinCoordinates(array $coordinatesArray): string
    {
        $pointsString = '';
        for ($i = 0, $n = count($coordinatesArray); $i < $n; ++$i) {
            if ($i > 0) {
                // join coordinates with ',' and points (2 coordinates) with ' '
                $pointsString .= $i % 2 === 1 ? ',' : ' ';
            }
            $pointsString .= $coordinatesArray[$i];
        }
        return $pointsString;
    }

    private static function splitPoints(?string $pointsString): array
    {
        $pointsArray = [];
        $coords = self::splitCoordinates($pointsString);
        for ($i = 0, $n = count($coords); $i + 1 < $n; $i += 2) {
            $pointsArray[] = [
                (float) $coords[$i],
                (float) $coords[$i + 1],
            ];
        }
        return $pointsArray;
    }

    private static function joinPoints(array $pointsArray): string
    {
        $pointsString = '';
        foreach ($pointsArray as $point) {
            if (count($point) < 2) {
                break;
            }
            if ($pointsString !== '') {
                $pointsString .= ' ';
            }
            $pointsString .= $point[0] . ',' . $point[1];
        }
        return $pointsString;
    }
}
