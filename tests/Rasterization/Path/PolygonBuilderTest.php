<?php

namespace SVG;

use SVG\Rasterization\Path\PolygonBuilder;

/**
 * @coversDefaultClass \SVG\Rasterization\Path\PolygonBuilder
 * @covers ::<!public>
 *
 * @SuppressWarnings(PHPMD)
 */
class PolygonBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers ::__construct
     */
    public function test__construct()
    {
        // should set position to origin by default
        $obj = new PolygonBuilder();
        $this->assertSame(array(0.0, 0.0), $obj->getPosition());

        // should use provided coordinates as origin
        $obj = new PolygonBuilder(37.1, 42.2);
        $this->assertSame(array(37.1, 42.2), $obj->getPosition());
    }

    /**
     * @covers ::build
     */
    public function testBuild()
    {
        // should return an array of float 2-tuples
        $obj = new PolygonBuilder();
        $obj->addPoint(10, 20);
        $obj->addPoint(37.1, 42.2);
        $this->assertSame(array(
            array(10, 20),
            array(37.1, 42.2),
        ), $obj->build());
    }

    /**
     * @covers ::getFirstPoint
     */
    public function testGetFirstPoint()
    {
        $obj = new PolygonBuilder();

        // should return null if no points exist
        $this->assertNull($obj->getFirstPoint());

        // should return the first point
        $obj->addPoint(10, 20);
        $obj->addPoint(37.1, 42.2);
        $this->assertSame(array(10, 20), $obj->getFirstPoint());
    }

    /**
     * @covers ::getLastPoint
     */
    public function testGetLastPoint()
    {
        $obj = new PolygonBuilder();

        // should return null if no points exist
        $this->assertNull($obj->getLastPoint());

        // should return the last point
        $obj->addPoint(10, 20);
        $obj->addPoint(37.1, 42.2);
        $this->assertSame(array(37.1, 42.2), $obj->getLastPoint());
    }

    /**
     * @covers ::getPosition
     */
    public function testGetPosition()
    {
        $obj = new PolygonBuilder(10, 20);

        // should return constructor coordinates at first
        $this->assertSame(array(10, 20), $obj->getPosition());

        // should return the last point added
        $obj->addPoint(37.1, 42.2);
        $this->assertSame(array(37.1, 42.2), $obj->getPosition());
    }

    /**
     * @covers ::addPoint
     */
    public function testAddPoint()
    {
        $obj = new PolygonBuilder();

        // should add the coordinates
        $obj->addPoint(10, 20);
        $this->assertSame(array(
            array(10, 20),
        ), $obj->build());
        $obj->addPoint(37.1, 42.2);
        $this->assertSame(array(
            array(10, 20),
            array(37.1, 42.2),
        ), $obj->build());

        // should use current position when null given instead of coordinate
        $obj->addPoint(0, null);
        $obj->addPoint(null, 0);
        $this->assertSame(array(
            array(10, 20),
            array(37.1, 42.2),
            array(0, 42.2),
            array(0, 0),
        ), $obj->build());
    }

    /**
     * @covers ::addPointRelative
     */
    public function testAddPointRelative()
    {
        $obj = new PolygonBuilder();

        // should add relative to the last point
        $obj->addPointRelative(10, 20);
        $obj->addPointRelative(37.1, 42.2);
        $obj->addPointRelative(0, 0);
        $this->assertSame(array(
            array(10.0, 20.0),
            array(47.1, 62.2),
            array(47.1, 62.2),
        ), $obj->build());

        // should treat null the same as 0
        $obj->addPointRelative(null, null);
        $this->assertSame(array(
            array(10.0, 20.0),
            array(47.1, 62.2),
            array(47.1, 62.2),
            array(47.1, 62.2),
        ), $obj->build());
    }

    /**
     * @covers ::addPoints
     */
    public function testAddPoints()
    {
        $obj = new PolygonBuilder();

        $obj->addPoint(10, 20);

        // should add the points
        $obj->addPoints(array(
            array(47.1, 62.2),
            array(100, 200),
        ));
        $this->assertSame(array(
            array(10, 20),
            array(47.1, 62.2),
            array(100, 200),
        ), $obj->build());
    }
}
