<?php

namespace SVG\Tests\Rasterization\Path;

use PHPUnit\Framework\TestCase;
use SVG\Rasterization\Path\PolygonBuilder;

/**
 * @coversDefaultClass \SVG\Rasterization\Path\PolygonBuilder
 * @covers ::<!public>
 *
 * @SuppressWarnings(PHPMD)
 */
class PolygonBuilderTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function test__construct(): void
    {
        // should set position to origin by default
        $obj = new PolygonBuilder();
        $this->assertSame([0.0, 0.0], $obj->getPosition());

        // should use provided coordinates as origin
        $obj = new PolygonBuilder(37.1, 42.2);
        $this->assertSame([37.1, 42.2], $obj->getPosition());
    }

    /**
     * @covers ::build
     */
    public function testBuild(): void
    {
        // should return an array of float 2-tuples
        $obj = new PolygonBuilder();
        $obj->addPoint(10, 20);
        $obj->addPoint(37.1, 42.2);
        $this->assertSame([
            [10.0, 20.0],
            [37.1, 42.2],
        ], $obj->build());
    }

    /**
     * @covers ::getFirstPoint
     */
    public function testGetFirstPoint(): void
    {
        $obj = new PolygonBuilder();

        // should return null if no points exist
        $this->assertNull($obj->getFirstPoint());

        // should return the first point
        $obj->addPoint(10, 20);
        $obj->addPoint(37.1, 42.2);
        $this->assertSame([10.0, 20.0], $obj->getFirstPoint());
    }

    /**
     * @covers ::getLastPoint
     */
    public function testGetLastPoint(): void
    {
        $obj = new PolygonBuilder();

        // should return null if no points exist
        $this->assertNull($obj->getLastPoint());

        // should return the last point
        $obj->addPoint(10, 20);
        $obj->addPoint(37.1, 42.2);
        $this->assertSame([37.1, 42.2], $obj->getLastPoint());
    }

    /**
     * @covers ::getPosition
     */
    public function testGetPosition(): void
    {
        $obj = new PolygonBuilder(10, 20);

        // should return constructor coordinates at first
        $this->assertSame([10.0, 20.0], $obj->getPosition());

        // should return the last point added
        $obj->addPoint(37.1, 42.2);
        $this->assertSame([37.1, 42.2], $obj->getPosition());
    }

    /**
     * @covers ::addPoint
     */
    public function testAddPoint(): void
    {
        $obj = new PolygonBuilder();

        // should add the coordinates
        $obj->addPoint(10, 20);
        $this->assertSame([
            [10.0, 20.0],
        ], $obj->build());
        $obj->addPoint(37.1, 42.2);
        $this->assertSame([
            [10.0, 20.0],
            [37.1, 42.2],
        ], $obj->build());

        // should use current position when null given instead of coordinate
        $obj->addPoint(0, null);
        $obj->addPoint(null, 0);
        $this->assertSame([
            [10.0, 20.0],
            [37.1, 42.2],
            [0.0, 42.2],
            [0.0, 0.0],
        ], $obj->build());
    }

    /**
     * @covers ::addPointRelative
     */
    public function testAddPointRelative(): void
    {
        $obj = new PolygonBuilder();

        // should add relative to the last point
        $obj->addPointRelative(10, 20);
        $obj->addPointRelative(37.1, 42.2);
        $obj->addPointRelative(0, 0);
        $this->assertSame([
            [10.0, 20.0],
            [47.1, 62.2],
            [47.1, 62.2],
        ], $obj->build());

        // should treat null the same as 0
        $obj->addPointRelative(null, null);
        $this->assertSame([
            [10.0, 20.0],
            [47.1, 62.2],
            [47.1, 62.2],
            [47.1, 62.2],
        ], $obj->build());
    }

    /**
     * @covers ::addPoints
     */
    public function testAddPoints(): void
    {
        $obj = new PolygonBuilder();

        $obj->addPoint(10, 20);

        // should add the points
        $obj->addPoints([
            [47.1, 62.2],
            [100, 200],
        ]);
        $this->assertSame([
            [10.0, 20.0],
            [47.1, 62.2],
            [100, 200],
        ], $obj->build());
    }
}
