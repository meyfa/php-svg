<?php

namespace SVG;

use SVG\Nodes\Structures\SVGDocumentFragment;

/**
 * @SuppressWarnings(PHPMD)
 */
 class SVGDocumentFragmentTest extends \PHPUnit\Framework\TestCase
{
    public function test__construct()
    {
        // should not be root or set any attributes by default
        $obj = new SVGDocumentFragment();
        $this->assertFalse($obj->isRoot());
        $this->assertSame(array(), $obj->getSerializableAttributes());

        // should set root, width, height when provided
        $obj = new SVGDocumentFragment(true, 37, 42);
        $this->assertTrue($obj->isRoot());
        $this->assertSame('37', $obj->getWidth());
        $this->assertSame('42', $obj->getHeight());

        // should set namespaces when provided
        $ns = array(
            'xmlns:foobar' => 'foobar-namespace',
        );
        $obj = new SVGDocumentFragment(true, 37, 42, $ns);
        $this->assertArraySubset($ns, $obj->getSerializableAttributes());
    }

    public function testGetWidth()
    {
        $obj = new SVGDocumentFragment();

        // should return the attribute
        $obj->setAttribute('width', 42);
        $this->assertSame('42', $obj->getWidth());
    }

    public function testSetWidth()
    {
        $obj = new SVGDocumentFragment();

        // should update the attribute
        $obj->setWidth(42);
        $this->assertSame('42', $obj->getAttribute('width'));
    }

    public function testGetHeight()
    {
        $obj = new SVGDocumentFragment();

        // should return the attribute
        $obj->setAttribute('height', 42);
        $this->assertSame('42', $obj->getHeight());
    }

    public function testSetHeight()
    {
        $obj = new SVGDocumentFragment();

        // should update the attribute
        $obj->setHeight(42);
        $this->assertSame('42', $obj->getAttribute('height'));
    }

    public function testGetComputedStyle()
    {
        $obj = new SVGDocumentFragment();

        // should return initial styles
        $this->assertSame('#000000', $obj->getComputedStyle('fill'));
        $this->assertSame('none', $obj->getComputedStyle('stroke'));
        $this->assertSame('1', $obj->getComputedStyle('stroke-width'));
        $this->assertSame('1', $obj->getComputedStyle('opacity'));

        // should return null for non-defined styles
        $this->assertNull($obj->getComputedStyle('undefined-test-style'));

        // should return explicitly set style over initial style
        $obj->setStyle('fill', '#FFFFFF');
        $this->assertSame('#FFFFFF', $obj->getComputedStyle('fill'));
    }

    public function testGetSerializableAttributes()
    {
        // should be empty by default
        $obj = new SVGDocumentFragment();
        $this->assertSame(array(), $obj->getSerializableAttributes());

        // should return previously defined properties
        $obj = new SVGDocumentFragment();
        $obj->setAttribute('id', 'test');
        $this->assertSame(array(
            'id' => 'test',
        ), $obj->getSerializableAttributes());

        // should include width and height when set
        $obj = new SVGDocumentFragment(false, 100, 200);
        $obj->setHeight(300);
        $this->assertSame(array(
            'width' => '100',
            'height' => '300',
        ), $obj->getSerializableAttributes());

        // should not include width/height when set to '100%'
        $obj = new SVGDocumentFragment(false, '100%', '100%');
        $this->assertSame(array(), $obj->getSerializableAttributes());

        // should include 'xmlns' and 'xmlns:xlink' namespaces for root
        $obj = new SVGDocumentFragment(true);
        $this->assertSame(array(
            'xmlns' => 'http://www.w3.org/2000/svg',
            'xmlns:xlink' => 'http://www.w3.org/1999/xlink',
        ), $obj->getSerializableAttributes());

        // should include additional namespaces
        $ns = array(
            'foo' => 'test-ns-foo',
            'xmlns:bar' => 'test-ns-bar',
        );
        $obj = new SVGDocumentFragment(true, null, null, $ns);
        $this->assertSame(array(
            'xmlns' => 'http://www.w3.org/2000/svg',
            'xmlns:xlink' => 'http://www.w3.org/1999/xlink',
            'xmlns:foo' => 'test-ns-foo',
            'xmlns:bar' => 'test-ns-bar',
        ), $obj->getSerializableAttributes());

        // should override 'xmlns' unprefixed when provided
        $obj = new SVGDocumentFragment(true, null, null, array(
            'xmlns' => 'xmlns-override',
        ));
        $this->assertSame(array(
            'xmlns' => 'xmlns-override',
            'xmlns:xlink' => 'http://www.w3.org/1999/xlink',
        ), $obj->getSerializableAttributes());

        // should treat empty namespace string like 'xmlns'
        $obj = new SVGDocumentFragment(true, null, null, array(
            '' => 'xmlns-override',
        ));
        $this->assertSame(array(
            'xmlns' => 'xmlns-override',
            'xmlns:xlink' => 'http://www.w3.org/1999/xlink',
        ), $obj->getSerializableAttributes());
    }

    public function testGetElementById()
    {
        // should return null if not found
        $obj = new SVGDocumentFragment();
        $this->assertNull($obj->getElementById('foobar'));

        // should return document fragment if id matches
        $obj = new SVGDocumentFragment();
        $obj->setAttribute('id', 'foobar');
        $this->assertSame($obj, $obj->getElementById('foobar'));

        // should return first matching descendant (tree order)
        $obj = new SVGDocumentFragment();
        $obj->addChild(
            // <container>
            $this->getMockForAbstractClass('\SVG\Nodes\SVGNodeContainer')->addChild(
                // <node />
                $this->getMockForAbstractClass('\SVG\Nodes\SVGNode')
            )->addChild(
                // <container>
                $this->getMockForAbstractClass('\SVG\Nodes\SVGNodeContainer')->addChild(
                    // <node id="foobar" />
                    $expected = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode')
                        ->setAttribute('id', 'foobar')
                )
                // </container>
            )
            // </container>
        );
        $obj->addChild(
            // <container>
            $this->getMockForAbstractClass('\SVG\Nodes\SVGNodeContainer')->addChild(
                // <node id="foobar" />
                $this->getMockForAbstractClass('\SVG\Nodes\SVGNode')
                    ->setAttribute('id', 'foobar')
            )
            // </container>
        );
        $this->assertSame($expected, $obj->getElementById('foobar'));
    }
}
