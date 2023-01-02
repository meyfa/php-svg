<?php

namespace SVG\Rasterization\Path;

/**
 * This is a helper class for simple polygon construction through sequentially
 * adding absolute and relative points.
 * Relative points are resolved against a starting position and/or the previous
 * point(s), resulting in an array of only absolute coordinates when built.
 */
class PolygonBuilder
{
    /**
     * @var array[] $points The polygon being built (array of float 2-tuples).
     */
    private $points = [];

    /**
     * @var float $posX The current x position.
     */
    private $posX;
    /**
     * @var float $posY The current y position.
     */
    private $posY;

    /**
     * @param float $posX The starting x position.
     * @param float $posY The starting y position.
     */
    public function __construct(float $posX = 0.0, float $posY = 0.0)
    {
        $this->posX = $posX;
        $this->posY = $posY;
    }

    /**
     * Method for obtaining the built polygon array.
     *
     * @return array[] An array of absolute points (which are float 2-tuples).
     */
    public function build(): array
    {
        return $this->points;
    }

    /**
     * Finds the very first point in this polygon, or null if none exist.
     *
     * @return float[]|null The first point, or null.
     */
    public function getFirstPoint(): ?array
    {
        if (empty($this->points)) {
            return null;
        }
        return $this->points[0];
    }

    /**
     * Finds the very last point in this polygon, or null if none exist.
     *
     * @return float[]|null The last point, or null.
     */
    public function getLastPoint(): ?array
    {
        if (empty($this->points)) {
            return null;
        }
        return $this->points[count($this->points) - 1];
    }

    /**
     * The position is either determined by the constructor in case no point was
     * added yet, and otherwise by the last point's absolute coordinates.
     *
     * This method is similar to `getLastPoint()`, with the difference that
     * the starting position is returned instead of null.
     *
     * @return float[] The current position (either last point, or initial pos).
     */
    public function getPosition(): array
    {
        return [$this->posX, $this->posY];
    }

    /**
     * Appends a point with ABSOLUTE coordinates to the end of this polygon.
     *
     * Provide null for a coordinate to use the current position for that
     * coordinate.
     *
     * @param float|null $x The point's absolute x coordinate.
     * @param float|null $y The point's absolute y coordinate.
     *
     * @return void
     */
    public function addPoint(?float $x, ?float $y): void
    {
        $x = $x ?? $this->posX;
        $y = $y ?? $this->posY;

        $this->points[] = [$x, $y];

        $this->posX = $x;
        $this->posY = $y;
    }

    /**
     * Appends a point with RELATIVE coordinates to the end of this polygon.
     *
     * The coordinates are resolved against the current position.
     * Providing null for a coordinate is the same as providing a value of 0.
     *
     * @see PolygonBuilder::getPosition() For more info on relative points.
     *
     * @param float|null $x The point's relative x coordinate.
     * @param float|null $y The point's relative y coordinate.
     *
     * @return void
     */
    public function addPointRelative(?float $x, ?float $y): void
    {
        $this->posX += $x ?: 0;
        $this->posY += $y ?: 0;

        $this->points[] = [$this->posX, $this->posY];
    }

    /**
     * Appends multiple points with ABSOLUTE coordinates to this polygon.
     *
     * @param array[] $points A point array (array of float 2-tuples).
     *
     * @return void
     */
    public function addPoints(array $points): void
    {
        $this->points = array_merge($this->points, $points);

        $endPoint = $this->points[count($this->points) - 1];
        $this->posX = $endPoint[0];
        $this->posY = $endPoint[1];
    }
}
