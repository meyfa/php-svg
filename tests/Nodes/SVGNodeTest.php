<?php

use SVG\Nodes\SVGNode;

class SVGNodeSubclass extends SVGNode
{
    const TAG_NAME = 'test_subclass';

    /**
     * @SuppressWarnings("unused")
     */
    public function rasterize(\SVG\Rasterization\SVGRasterizer $rasterizer)
    {
    }
}

/**
 * @SuppressWarnings(PHPMD)
 */
class SVGNodeTest extends PHPUnit_Framework_TestCase
{
    public function testConstructFromAttributes()
    {
        $obj = SVGNodeSubclass::constructFromAttributes(array());

        // should construct child class
        $this->assertInstanceOf('SVGNodeSubclass', $obj);
    }

    public function testGetName()
    {
        $obj = new SVGNodeSubclass();

        // should return child class const
        $this->assertSame(SVGNodeSubclass::TAG_NAME, $obj->getName());
    }

    public function testGetStyle()
    {
        $obj = new SVGNodeSubclass();

        // should return null for undefined properties
        $this->assertSame(null, $obj->getStyle('fill'));
    }

    public function testSetStyle()
    {
        $obj = new SVGNodeSubclass();

        // should set properties
        $obj->setStyle('fill', '#FFFFFF');
        $this->assertSame('#FFFFFF', $obj->getStyle('fill'));

        // should unset properties when given null
        $obj->setStyle('fill', null);
        $this->assertSame(null, $obj->getStyle('fill'));

        // should unset properties when given ''
        $obj->setStyle('fill', '');
        $this->assertSame(null, $obj->getStyle('fill'));

        // should convert value to a string
        $obj->setStyle('width', 42);
        $this->assertSame('42', $obj->getStyle('width'));

        // should not treat 0 as an empty value
        $obj->setStyle('width', 0);
        $this->assertSame('0', $obj->getStyle('width'));
    }

    public function testRemoveStyle()
    {
        $obj = new SVGNodeSubclass();

        // should remove the property
        $obj->setStyle('fill', '#FFFFFF');
        $obj->removeStyle('fill');
        $this->assertSame(null, $obj->getStyle('fill'));
    }

    public function testGetAttribute()
    {
        $obj = new SVGNodeSubclass();

        // should return null for undefined properties
        $this->assertSame(null, $obj->getAttribute('x'));
    }

    public function testSetAttribute()
    {
        $obj = new SVGNodeSubclass();

        // should set properties
        $obj->setAttribute('x', '100%');
        $this->assertSame('100%', $obj->getAttribute('x'));

        // should unset properties when given null
        $obj->setAttribute('x', null);
        $this->assertSame(null, $obj->getAttribute('x'));

        // should not unset properties when given ''
        $obj->setAttribute('x', '');
        $this->assertSame('', $obj->getAttribute('x'));

        // should convert value to a string
        $obj->setAttribute('x', 42);
        $this->assertSame('42', $obj->getAttribute('x'));

        // should not treat 0 as an empty value
        $obj->setAttribute('x', 0);
        $this->assertSame('0', $obj->getAttribute('x'));
    }

    public function testRemoveAttribute()
    {
        $obj = new SVGNodeSubclass();

        // should remove the property
        $obj->setAttribute('x', '100%');
        $obj->removeAttribute('x');
        $this->assertSame(null, $obj->getAttribute('x'));
    }

    public function testGetSerializableAttributes()
    {
        $obj = new SVGNodeSubclass();

        // should return previously defined properties
        $obj->setAttribute('x', 0);
        $obj->setAttribute('y', 0);
        $obj->setAttribute('width', '100%');
        $this->assertEquals(array(
            'x' => '0',
            'y' => '0',
            'width' => '100%',
        ), $obj->getSerializableAttributes());
    }

    public function testGetSerializableStyles()
    {
        $obj = new SVGNodeSubclass();

        // should return previously defined properties
        $obj->setStyle('fill', '#FFFFFF');
        $obj->setStyle('width', 42);
        $this->assertEquals(array(
            'fill' => '#FFFFFF',
            'width' => '42',
        ), $obj->getSerializableStyles());
    }
}
