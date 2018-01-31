<?php

use SVG\Nodes\SVGNodeContainer;

class SVGNodeContainerSubclass extends SVGNodeContainer
{
    const TAG_NAME = 'test_subclass';
}

/**
 * @SuppressWarnings(PHPMD)
 */
class SVGNodeContainerTest extends PHPUnit_Framework_TestCase
{
    public function testAddChild()
    {
        $obj = new SVGNodeContainerSubclass();
        $obj2 = new SVGNodeContainerSubclass();
        $child = new SVGNodeContainerSubclass();

        // should add the child
        $obj->addChild($child);
        $this->assertSame(1, $obj->countChildren());
        $this->assertSame($child, $obj->getChild(0));

        // should set the child's parent property
        $this->assertSame($obj, $child->getParent());

        // should not add the child twice
        $obj->addChild($child);
        $this->assertSame(1, $obj->countChildren());

        // should not add itself as a child
        $obj->addChild($obj);
        $this->assertSame(1, $obj->countChildren());

        // should remove the child from its previous parent
        $obj2->addChild($child);
        $this->assertSame(0, $obj->countChildren());
    }

    public function testRemoveChild()
    {
        $obj = new SVGNodeContainerSubclass();
        $child = new SVGNodeContainerSubclass();

        // should do nothing for nonexistent instances
        $obj->removeChild($child);

        // should remove by instance
        $obj->addChild($child);
        $obj->removeChild($child);
        $this->assertSame(0, $obj->countChildren());

        // should remove by index
        $obj->addChild($child);
        $obj->removeChild(0);
        $this->assertSame(0, $obj->countChildren());

        // should set child's parent to null
        $this->assertNull($child->getParent());
    }

    public function testRasterize()
    {
        $obj = new SVGNodeContainerSubclass();

        $mockChild = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $obj->addChild($mockChild);

        $rast = $this->getMockBuilder('\SVG\Rasterization\SVGRasterizer')
            ->disableOriginalConstructor()
            ->getMock();

        // should call children's rasterize method
        $mockChild->expects($this->once())->method('rasterize');
        $obj->rasterize($rast);

        // should not rasterize with 'display: none' style
        $obj->setStyle('display', 'none');
        $obj->rasterize($rast);
    }

    public function testGetElementsByTagName()
    {
        $obj = new SVGNodeContainerSubclass();
        $obj->addChild(
            $root_0 = (new \SVG\Nodes\Structures\SVGGroup())->addChild(
                $root_0_0 = new \SVG\Nodes\Shapes\SVGLine()
            )->addChild(
                $root_0_1 = new \SVG\Nodes\Shapes\SVGRect()
            )
        );
        $obj->addChild(
            $root_1 = (new \SVG\Nodes\Structures\SVGGroup())->addChild(
                $root_1_0 = (new \SVG\Nodes\Structures\SVGGroup())->addChild(
                    $root_1_0_0 = new \SVG\Nodes\Shapes\SVGRect()
                )
            )->addChild(
                $root_1_1 = new \SVG\Nodes\Shapes\SVGRect()
            )
        );

        // should not return itself
        $this->assertSame(array(), $obj->getElementsByTagName('test_subclass'));
        $this->assertNotContains($obj, $obj->getElementsByTagName('*'));

        // should return specific tags
        $this->assertSame(array(
            $root_0_1, $root_1_0_0, $root_1_1,
        ), $obj->getElementsByTagName('rect'));

        // should return all descendants for '*'
        $this->assertSame(array(
            $root_0, $root_0_0, $root_0_1,
            $root_1, $root_1_0, $root_1_0_0, $root_1_1,
        ), $obj->getElementsByTagName('*'));
    }

    public function testGetElementsByClassName()
    {
        $obj = (new SVGNodeContainerSubclass());
        $obj->addChild(
            $root_0 = (new \SVG\Nodes\Structures\SVGGroup())->addChild(
                $root_0_0 = new \SVG\Nodes\Shapes\SVGRect()
            )->addChild(
                $root_0_1 = new \SVG\Nodes\Shapes\SVGRect()
            )
        );
        $obj->addChild(
            $root_1 = (new \SVG\Nodes\Structures\SVGGroup())->addChild(
                $root_1_0 = (new \SVG\Nodes\Structures\SVGGroup())->addChild(
                    $root_1_0_0 = new \SVG\Nodes\Shapes\SVGRect()
                )
            )->addChild(
                $root_1_1 = new \SVG\Nodes\Shapes\SVGRect()
            )
        );

        $obj->setAttribute('class', 'foo bar baz');
        $root_0->setAttribute('class', 'a');
        // $root_0_0 left out on purpose
        $root_0_1->setAttribute('class', 'foo');
        $root_1->setAttribute('class', ' a  b    foo ');
        $root_1_0->setAttribute('class', 'foobar');
        $root_1_0_0->setAttribute('class', 'foo bar');
        $root_1_1->setAttribute('class', 'bar foo baz');

        // should not return itself
        $this->assertNotContains(array(),
            $obj->getElementsByClassName('foo'));
        $this->assertNotContains(array(),
            $obj->getElementsByClassName('foo bar baz'));
        $this->assertNotContains(array(),
            $obj->getElementsByClassName(array('foo')));

        // should find by single class name
        $this->assertSame(array(
            $root_0_1, $root_1, $root_1_0_0, $root_1_1,
        ), $obj->getElementsByClassName('foo'));

        // should find by multiple class names
        $this->assertSame(array(
            $root_1_0_0, $root_1_1,
        ), $obj->getElementsByClassName('foo  bar '));

        // should work with arrays
        $this->assertSame(array(
            $root_1_0_0, $root_1_1,
        ), $obj->getElementsByClassName(array('foo', 'bar')));
    }
}
