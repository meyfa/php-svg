<?php

use SVG\Nodes\SVGNodeContainer;

class SVGNodeContainerSubclass extends SVGNodeContainer
{
    const TAG_NAME = 'test_subclass';
}

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
        $this->assertSame(null, $child->getParent());
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
}
