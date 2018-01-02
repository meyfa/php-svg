<?php

use SVG\Nodes\Shapes\SVGPolygonalShape;

/**
 * @SuppressWarnings(PHPMD)
 */
class SVGPolygonalShapeSubclass extends SVGPolygonalShape
{
    const TAG_NAME = 'test_subclass';

    public function __construct($points)
    {
        parent::__construct($points);
    }

    public function rasterize(\SVG\Rasterization\SVGRasterizer $rasterizer)
    {
    }
}

/**
 * @SuppressWarnings(PHPMD)
 */
 class SVGPolygonalShapeTest extends PHPUnit_Framework_TestCase
{
    public function test__construct()
    {
        // should set provided points
        $points = array(
            array(42.5, 42.5),
            array(37, 37),
        );
        $obj = new SVGPolygonalShapeSubclass($points);
        $this->assertSame($points, $obj->getPoints());
    }

    public function testConstructFromAttributes()
    {
        // should set empty points by default
        $obj = SVGPolygonalShapeSubclass::constructFromAttributes(array());
        $this->assertSame(0, $obj->countPoints());

        // should set points when provided
        $obj = SVGPolygonalShapeSubclass::constructFromAttributes(array(
            'points' => '1,1 2,2 3,3',
        ));
        $this->assertSame(3, $obj->countPoints());
    }

    public function testAddPoint()
    {
        $obj = new SVGPolygonalShapeSubclass(array());

        // should support 2 floats
        $obj->addPoint(42.5, 42.5);
        $this->assertSame(array(
            array(42.5, 42.5),
        ), $obj->getPoints());

        // should support an array
        $obj->addPoint(array(37, 37));
        $this->assertSame(array(
            array(42.5, 42.5),
            array(37, 37),
        ), $obj->getPoints());
    }

    public function testRemovePoint()
    {
        $obj = new SVGPolygonalShapeSubclass(array(
            array(42.5, 42.5),
            array(37, 37),
        ));

        // should remove points by index
        $obj->removePoint(0);
        $this->assertSame(array(
            array(37, 37),
        ), $obj->getPoints());

        $obj->removePoint(0);
        $this->assertSame(array(), $obj->getPoints());
    }

    public function testSetPoint()
    {
        $obj = new SVGPolygonalShapeSubclass(array(
            array(42.5, 42.5),
            array(37, 37),
        ));

        // should replace the point at the given index
        $obj->setPoint(1, array(100, 100));
        $this->assertSame(array(
            array(42.5, 42.5),
            array(100, 100),
        ), $obj->getPoints());
    }

    public function testGetSerializableAttributes()
    {
        $obj = new SVGPolygonalShapeSubclass(array(
            array(42.5, 43.5),
            array(37, 38),
        ));
        $obj->setAttribute('id', 'poly');

        $attrs = $obj->getSerializableAttributes();

        // should include other attributes
        $this->assertArraySubset(array('id' => 'poly'), $attrs);

        // should include 'points' attribute
        $this->assertArrayHasKey('points', $attrs);

        // should stringify correctly
        $this->assertSame('42.5,43.5 37,38', $attrs['points']);
    }
}
