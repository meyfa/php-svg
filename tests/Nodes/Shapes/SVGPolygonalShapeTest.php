<?php

namespace SVG;

use SVG\Nodes\Shapes\SVGPolygonalShape;

/**
 * @SuppressWarnings(PHPMD)
 */
class SVGPolygonalShapeSubclass extends SVGPolygonalShape
{
    const TAG_NAME = 'test_subclass';

    public function __construct(array $points = null)
    {
        parent::__construct($points);
    }

    public function rasterize(\SVG\Rasterization\SVGRasterizer $rasterizer)
    {
    }
}

/**
 * @coversDefaultClass \SVG\Nodes\Shapes\SVGPolygonalShape
 * @covers ::<!public>
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGPolygonalShapeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers ::__construct
     */
    public function test__construct()
    {
        // should not set attribute if nothing is provided
        $obj = new SVGPolygonalShapeSubclass();
        $this->assertNull($obj->getAttribute('points'));

        // should set attribute to '' if empty array is provided
        $obj = new SVGPolygonalShapeSubclass(array());
        $this->assertSame('', $obj->getAttribute('points'));

        // should set provided points
        $points = array(
            array(42.5, 42.5),
            array(37, 37),
        );
        $obj = new SVGPolygonalShapeSubclass($points);
        $this->assertSame('42.5,42.5 37,37', $obj->getAttribute('points'));

        // should stop when invalid point is encountered
        $obj = new SVGPolygonalShapeSubclass(array(
            array(1, 2),
            array(3),
            array(4, 5),
        ));
        $this->assertSame('1,2', $obj->getAttribute('points'));
    }

    /**
     * @covers ::addPoint
     */
    public function testAddPoint()
    {
        $obj = new SVGPolygonalShapeSubclass();

        // should support 2 floats
        $obj->addPoint(42.5, 43.5);
        $this->assertSame('42.5,43.5', $obj->getAttribute('points'));

        // should support an array
        $obj->addPoint(array(37, 38));
        $this->assertSame('42.5,43.5 37,38', $obj->getAttribute('points'));

        // should return same instance
        $this->assertSame($obj, $obj->addPoint(42, 37));

        // should notice attribute reset
        $obj->setAttribute('points', null);
        $obj->addPoint(10, 11);
        $this->assertSame('10,11', $obj->getAttribute('points'));

        // should not add whitespace to empty attribute
        $obj->setAttribute('points', '');
        $obj->addPoint(12, 13);
        $this->assertSame('12,13', $obj->getAttribute('points'));
    }

    /**
     * @covers ::removePoint
     */
    public function testRemovePoint()
    {
        $obj = new SVGPolygonalShapeSubclass(array(
            array(42.5, 43.5),
            array(37, 38),
        ));

        // should remove points by index
        $obj->removePoint(0);
        $this->assertSame('37,38', $obj->getAttribute('points'));
        $this->assertEquals(array(
            array(37, 38),
        ), $obj->getPoints());

        // should return same instance
        $this->assertSame($obj, $obj->removePoint(0));

        // should allow clearing points completely
        $this->assertSame('', $obj->getAttribute('points'));
        $this->assertSame(array(), $obj->getPoints());

        // should allow removing from middle
        $obj->setAttribute('points', '1,2 3,4 5,6');
        $obj->removePoint(1);
        $this->assertSame('1,2 5,6', $obj->getAttribute('points'));
    }

    /**
     * @covers ::countPoints
     */
    public function testCountPoints()
    {
        $obj = new SVGPolygonalShapeSubclass(array(
            array(42.5, 43.5),
            array(37, 38),
        ));

        // should return number of points
        $this->assertSame(2, $obj->countPoints());

        // should return 0 for missing attribute
        $obj->setAttribute('points', null);
        $this->assertSame(0, $obj->countPoints());

        // should return 0 for empty attribute
        $obj->setAttribute('points', '');
        $this->assertSame(0, $obj->countPoints());

        // should use attribute as source
        $obj->setAttribute('points', '1,2 3,4 5,6');
        $this->assertSame(3, $obj->countPoints());

        // should support a variety of separators
        $obj->setAttribute('points', '1,-2 3 -4  ,  5   -6  ,7,-8');
        $this->assertSame(4, $obj->countPoints());

        // can deal with an odd number of coordinates
        $obj->setAttribute('points', '1 2 3 4 5 6 7');
        $this->assertSame(3, $obj->countPoints());
    }

    /**
     * @covers ::getPoints
     */
    public function testGetPoints()
    {
        $obj = new SVGPolygonalShapeSubclass();

        // should return empty array for missing attribute
        $obj->setAttribute('points', null);
        $this->assertSame(array(), $obj->getPoints());

        // should return empty array for empty attribute
        $obj->setAttribute('points', '');
        $this->assertSame(array(), $obj->getPoints());

        // should parse attribute
        $obj->setAttribute('points', '42.5 43.5 37 38 -5 3');
        $this->assertEquals(array(
            array(42.5, 43.5),
            array(37, 38),
            array(-5, 3)
        ), $obj->getPoints());

        // should support comma delimiter
        $obj->setAttribute('points', '42.5 43.5 37,38,-5,3');
        $this->assertEquals(array(
            array(42.5, 43.5),
            array(37, 38),
            array(-5, 3)
        ), $obj->getPoints());
    }

    /**
     * @covers ::getPoint
     */
    public function testGetPoint()
    {
        $obj = new SVGPolygonalShapeSubclass(array(
            array(42.5, 43.5),
            array(37, 38),
        ));

        // should return point at index
        $this->assertEquals(array(42.5, 43.5), $obj->getPoint(0));
        $this->assertEquals(array(37, 38), $obj->getPoint(1));
    }

    /**
     * @covers ::setPoint
     */
    public function testSetPoint()
    {
        $obj = new SVGPolygonalShapeSubclass(array(
            array(42.5, 43.5),
            array(37, 38),
        ));

        // should replace the point at the given index
        $obj->setPoint(1, array(100, 200));
        $this->assertSame('42.5,43.5 100,200', $obj->getAttribute('points'));

        // should return same instance
        $this->assertSame($obj, $obj->setPoint(1, array(300, 400)));
    }
}
