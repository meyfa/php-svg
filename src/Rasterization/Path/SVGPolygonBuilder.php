<?php

namespace JangoBrick\SVG\Rasterization\Path;

class SVGPolygonBuilder
{
    private $points = array();
    private $posX, $posY;

    public function __construct($posX = 0, $posY = 0)
    {
        $this->posX = $posX;
        $this->posY = $posY;
    }



    public function build()
    {
        return $this->points;
    }



    public function getFirstPoint()
    {
        if (empty($this->points)) {
            return null;
        }
        return $this->points[0];
    }

    public function getLastPoint()
    {
        if (empty($this->points)) {
            return null;
        }
        return $this->points[count($this->points) - 1];
    }

    public function getPosition()
    {
        return array($this->posX, $this->posY);
    }



    public function addPoint($x, $y)
    {
        $x = isset($x) ? $x : $this->posX;
        $y = isset($y) ? $y : $this->posY;

        $this->points[] = array($x, $y);

        $this->posX = $x;
        $this->posY = $y;
    }

    public function addPointRelative($x, $y)
    {
        $x = $x ?: 0;
        $y = $y ?: 0;

        $this->posX += $x;
        $this->posY += $y;

        $this->points[] = array($this->posX, $this->posY);
    }



    public function addPoints(array $points)
    {
        $this->points = array_merge($this->points, $points);

        $endPoint = $this->points[count($this->points) - 1];
        $this->posX = $endPoint[0];
        $this->posY = $endPoint[1];
    }
}
