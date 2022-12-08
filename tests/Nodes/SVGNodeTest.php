<?php

namespace SVG;

use SVG\Nodes\SVGNode;

class SVGNodeSubclass extends SVGNode
{
    const TAG_NAME = 'test_subclass';

    /**
     * @inheritdoc
     */
    public function rasterize(\SVG\Rasterization\SVGRasterizer $rasterizer)
    {
    }
}

/**
 * @coversDefaultClass \SVG\Nodes\SVGNode
 * @covers ::<!public>
 * @covers ::__construct
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGNodeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers ::getName
     */
    public function testGetName()
    {
        $obj = new SVGNodeSubclass();

        // should return child class const
        $this->assertSame(SVGNodeSubclass::TAG_NAME, $obj->getName());
    }

    /**
     * @covers ::getParent
     */
    public function testGetParent()
    {
        $obj = new SVGNodeSubclass();

        // should return null when parentless
        $this->assertNull($obj->getParent());
    }

    /**
     * @covers ::getValue
     */
    public function testGetValue()
    {
        $obj = new SVGNodeSubclass();

        // should return empty string
        $this->assertSame('', $obj->getValue());
    }

    /**
     * @covers ::setValue
     */
    public function testSetValue()
    {
        $obj = new SVGNodeSubclass();

        // should update value
        $obj->setValue('hello world');
        $this->assertSame('hello world', $obj->getValue());

        // should treat null like empty string
        $obj->setValue(null);
        $this->assertSame('', $obj->getValue());

        // should return same instance
        $this->assertSame($obj, $obj->setValue('foo'));
    }

    /**
     * @covers ::getStyle
     */
    public function testGetStyle()
    {
        $obj = new SVGNodeSubclass();

        // should return null for undefined properties
        $this->assertNull($obj->getStyle('fill'));
    }

    /**
     * @covers ::setStyle
     */
    public function testSetStyle()
    {
        $obj = new SVGNodeSubclass();

        // should set properties
        $obj->setStyle('fill', '#FFFFFF');
        $this->assertSame('#FFFFFF', $obj->getStyle('fill'));

        // should trim whitespace around the value
        $obj->setStyle('fill', "  \n #FFFFFF \n  ");
        $this->assertSame('#FFFFFF', $obj->getStyle('fill'));

        // should unset properties when given null
        $obj->setStyle('fill', '#FFFFFF');
        $obj->setStyle('fill', null);
        $this->assertNull($obj->getStyle('fill'));

        // should unset properties when given ''
        $obj->setStyle('fill', '#FFFFFF');
        $obj->setStyle('fill', '');
        $this->assertNull($obj->getStyle('fill'));

        // should unset properties when given a whitespace-only string
        $obj->setStyle('fill', '#FFFFFF');
        $obj->setStyle('fill', "  \n  ");
        $this->assertNull($obj->getStyle('fill'));

        // should convert value to a string
        $obj->setStyle('width', 42);
        $this->assertSame('42', $obj->getStyle('width'));

        // should not treat 0 as an empty value
        $obj->setStyle('width', 0);
        $this->assertSame('0', $obj->getStyle('width'));

        // should return same instance
        $this->assertSame($obj, $obj->setStyle('fill', '#FFF'));
        $this->assertSame($obj, $obj->setStyle('fill', null));
    }

    /**
     * @covers ::removeStyle
     */
    public function testRemoveStyle()
    {
        $obj = new SVGNodeSubclass();

        // should remove the property
        $obj->setStyle('fill', '#FFFFFF');
        $obj->removeStyle('fill');
        $this->assertNull($obj->getStyle('fill'));

        // should return same instance
        $this->assertSame($obj, $obj->removeStyle('fill'));
    }

    /**
     * @covers ::getAttribute
     */
    public function testGetAttribute()
    {
        $obj = new SVGNodeSubclass();

        // should return null for undefined properties
        $this->assertNull($obj->getAttribute('x'));
    }

    /**
     * @covers ::setAttribute
     */
    public function testSetAttribute()
    {
        $obj = new SVGNodeSubclass();

        // should set properties
        $obj->setAttribute('x', '100%');
        $this->assertSame('100%', $obj->getAttribute('x'));

        // should unset properties when given null
        $obj->setAttribute('x', null);
        $this->assertNull($obj->getAttribute('x'));

        // should not unset properties when given ''
        $obj->setAttribute('x', '');
        $this->assertSame('', $obj->getAttribute('x'));

        // should convert value to a string
        $obj->setAttribute('x', 42);
        $this->assertSame('42', $obj->getAttribute('x'));

        // should not treat 0 as an empty value
        $obj->setAttribute('x', 0);
        $this->assertSame('0', $obj->getAttribute('x'));

        // should return same instance
        $this->assertSame($obj, $obj->setAttribute('x', 42));
        $this->assertSame($obj, $obj->setAttribute('x', null));
    }

    /**
     * @covers ::removeAttribute
     */
    public function testRemoveAttribute()
    {
        $obj = new SVGNodeSubclass();

        // should remove the property
        $obj->setAttribute('x', '100%');
        $obj->removeAttribute('x');
        $this->assertNull($obj->getAttribute('x'));

        // should return same instance
        $this->assertSame($obj, $obj->removeAttribute('x'));
    }

    /**
     * @covers ::getSerializableNamespaces
     */
    public function testGetSerializableNamespaces()
    {
        $obj = new SVGNodeSubclass();

        // should set namespaces when declared
        $ns = [
            'xmlns:foobar' => 'foobar-namespace',
        ];
        $obj->setNamespaces($ns);
        $this->assertSame($ns, $obj->getSerializableNamespaces());
    }

    /**
     * @covers ::getSerializableAttributes
     */
    public function testGetSerializableAttributes()
    {
        $obj = new SVGNodeSubclass();

        // should return previously defined properties
        $obj->setAttribute('x', 0);
        $obj->setAttribute('y', 0);
        $obj->setAttribute('width', '100%');
        $this->assertSame([
            'x' => '0',
            'y' => '0',
            'width' => '100%',
        ], $obj->getSerializableAttributes());
    }

    /**
     * @covers ::getSerializableStyles
     */
    public function testGetSerializableStyles()
    {
        $obj = new SVGNodeSubclass();

        // should return previously defined properties
        $obj->setStyle('fill', '#FFFFFF');
        $obj->setStyle('width', 42);
        $this->assertSame([
            'fill' => '#FFFFFF',
            'width' => '42',
        ], $obj->getSerializableStyles());
    }

    /**
     * @covers ::getViewBox
     */
    public function testGetViewBox()
    {
        $obj = new SVGNodeSubclass();

        // should return null for missing viewBox
        $this->assertNull($obj->getViewBox());

        // should return null for ill-formed viewBox
        $obj->setAttribute('viewBox', 'foobar');
        $this->assertNull($obj->getViewBox());
        $obj->setAttribute('viewBox', '37 42.25');
        $this->assertNull($obj->getViewBox());
        $obj->setAttribute('viewBox', '37, , , ');
        $this->assertNull($obj->getViewBox());

        // should return float array for well-formed viewBox
        $obj->setAttribute('viewBox', '37, 42.25, 100 200');
        $this->assertSame([37.0, 42.25, 100.0, 200.0], $obj->getViewBox());
        $obj->setAttribute('viewBox', '37, .25, 100 200');
        $this->assertSame([37.0, 0.25, 100.0, 200.0], $obj->getViewBox());

        // should ignore superfluous whitespace
        $obj->setAttribute('viewBox', "  \n 37, 42.25,\n 100 200 \n  ");
        $this->assertSame([37.0, 42.25, 100.0, 200.0], $obj->getViewBox());
    }
}
