<?php

namespace SVG;

use AssertGD\GDSimilarityConstraint;
use SVG\Nodes\Structures\SVGDocumentFragment;

/**
 * @coversDefaultClass \SVG\Nodes\Structures\SVGDocumentFragment
 * @covers ::<!public>
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGDocumentFragmentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers ::__construct
     */
    public function test__construct()
    {
        $container = new \SVG\Nodes\Structures\SVGGroup();

        // should not set any attributes by default
        $obj = new SVGDocumentFragment();
        $container->addChild($obj);
        $this->assertSame(array(), $obj->getSerializableAttributes());

        // should set width, height when provided
        $obj = new SVGDocumentFragment(37, 42);
        $this->assertSame('37', $obj->getWidth());
        $this->assertSame('42', $obj->getHeight());
    }

    /**
     * @covers ::isRoot
     */
    public function testIsRoot()
    {
        // should return true by default
        $obj = new SVGDocumentFragment();
        $this->assertTrue($obj->isRoot());

        // should return false if added to another container
        $container = new \SVG\Nodes\Structures\SVGGroup();
        $container->addChild($obj);
        $this->assertFalse($obj->isRoot());
    }

    /**
     * @covers ::getWidth
     */
    public function testGetWidth()
    {
        $obj = new SVGDocumentFragment();

        // should return the attribute
        $obj->setAttribute('width', 42);
        $this->assertSame('42', $obj->getWidth());
    }

    /**
     * @covers ::setWidth
     */
    public function testSetWidth()
    {
        $obj = new SVGDocumentFragment();

        // should update the attribute
        $obj->setWidth(42);
        $this->assertSame('42', $obj->getAttribute('width'));

        // should return same instance
        $this->assertSame($obj, $obj->setWidth(42));
    }

    /**
     * @covers ::getHeight
     */
    public function testGetHeight()
    {
        $obj = new SVGDocumentFragment();

        // should return the attribute
        $obj->setAttribute('height', 42);
        $this->assertSame('42', $obj->getHeight());
    }

    /**
     * @covers ::setHeight
     */
    public function testSetHeight()
    {
        $obj = new SVGDocumentFragment();

        // should update the attribute
        $obj->setHeight(42);
        $this->assertSame('42', $obj->getAttribute('height'));

        // should return same instance
        $this->assertSame($obj, $obj->setHeight(42));
    }

    /**
     * @covers ::getComputedStyle
     */
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

    /**
     * @covers ::getSerializableNamespaces
     */
    public function testGetSerializableNamespaces()
    {
        // should include 'xmlns' and 'xmlns:xlink' namespaces for root
        $obj = new SVGDocumentFragment();
        $this->assertEquals(array(
            '' => 'http://www.w3.org/2000/svg',
            'xlink' => 'http://www.w3.org/1999/xlink',
        ), $obj->getSerializableNamespaces());

        // should include additional namespaces
        $obj = new SVGDocumentFragment();
        $obj->setNamespaces(array(
            'xmlns:foo' => 'test-ns-foo',
            'xmlns:bar' => 'test-ns-bar',
        ));
        $this->assertEquals(array(
            '' => 'http://www.w3.org/2000/svg',
            'xlink' => 'http://www.w3.org/1999/xlink',
            'xmlns:foo' => 'test-ns-foo',
            'xmlns:bar' => 'test-ns-bar',
        ), $obj->getSerializableNamespaces());

        // should override 'xmlns' unprefixed when provided
        $obj = new SVGDocumentFragment();
        $obj->setNamespaces(array(
            '' => 'xmlns-override',
        ));
        $this->assertEquals(array(
            '' => 'xmlns-override',
            'xlink' => 'http://www.w3.org/1999/xlink',
        ), $obj->getSerializableNamespaces());
    }

    /**
     * @covers ::getSerializableAttributes
     */
    public function testGetSerializableAttributes()
    {
        $container = new \SVG\Nodes\Structures\SVGGroup();

        // should be empty by default
        $obj = new SVGDocumentFragment();
        $container->addChild($obj);
        $this->assertSame(array(), $obj->getSerializableAttributes());

        // should return previously defined properties
        $obj = new SVGDocumentFragment();
        $container->addChild($obj);
        $obj->setAttribute('id', 'test');
        $this->assertSame(array(
            'id' => 'test',
        ), $obj->getSerializableAttributes());

        // should include width and height when set
        $obj = new SVGDocumentFragment(100, 200);
        $container->addChild($obj);
        $obj->setHeight(300);
        $this->assertSame(array(
            'width' => '100',
            'height' => '300',
        ), $obj->getSerializableAttributes());

        // should not include width/height when set to '100%'
        $obj = new SVGDocumentFragment('100%', '100%');
        $container->addChild($obj);
        $this->assertSame(array(), $obj->getSerializableAttributes());
    }

    /**
     * @covers ::getElementById
     */
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

    // ---- THE FOLLOWING TESTS ARE INTEGRATION TESTS ----

    /**
     * @requires extension gd
     * @coversNothing
     */
    public function testRasterize_empty()
    {
        $obj = new SVGDocumentFragment('2px', '2px');

        $rasterizer = new \SVG\Rasterization\SVGRasterizer(
            $obj->getWidth(),   // doc width
            $obj->getHeight(),  // doc height
            $obj->getViewBox(), // viewBox
            4,                  // result width
            8                   // result height
        );
        $obj->rasterize($rasterizer);
        $img = $rasterizer->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/empty-4x8.png'));
    }

    /**
     * @requires extension gd
     * @coversNothing
     */
    public function testRasterize_object_unscaled()
    {
        $obj = new SVGDocumentFragment('20px', '40px');

        $rect = new \SVG\Nodes\Shapes\SVGRect('5px', '5px', '10px', '30px');
        $rect->setStyle('fill', '#FF0000');
        $obj->addChild($rect);

        $rasterizer = new \SVG\Rasterization\SVGRasterizer(
            $obj->getWidth(),   // doc width
            $obj->getHeight(),  // doc height
            $obj->getViewBox(), // viewBox
            20,                 // result width
            40                  // result height
        );
        $obj->rasterize($rasterizer);
        $img = $rasterizer->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/rect-20x40.png'));
    }

    /**
     * @requires extension gd
     * @coversNothing
     */
    public function testRasterize_object_scaledUp()
    {
        $obj = new SVGDocumentFragment('10px', '20px');

        $rect = new \SVG\Nodes\Shapes\SVGRect('2.5px', '2.5px', '5px', '15px');
        $rect->setStyle('fill', '#FF0000');
        $obj->addChild($rect);

        $rasterizer = new \SVG\Rasterization\SVGRasterizer(
            $obj->getWidth(),   // doc width
            $obj->getHeight(),  // doc height
            $obj->getViewBox(), // viewBox
            20,                 // result width
            40                  // result height
        );
        $obj->rasterize($rasterizer);
        $img = $rasterizer->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/rect-20x40.png'));
    }

    /**
     * @requires extension gd
     * @coversNothing
     */
    public function testRasterize_object_scaledDown()
    {
        $obj = new SVGDocumentFragment('40px', '80px');

        $rect = new \SVG\Nodes\Shapes\SVGRect('10px', '10px', '20px', '60px');
        $rect->setStyle('fill', '#FF0000');
        $obj->addChild($rect);

        $rasterizer = new \SVG\Rasterization\SVGRasterizer(
            $obj->getWidth(),   // doc width
            $obj->getHeight(),  // doc height
            $obj->getViewBox(), // viewBox
            20,                 // result width
            40                  // result height
        );
        $obj->rasterize($rasterizer);
        $img = $rasterizer->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/rect-20x40.png'));
    }

    /**
     * @requires extension gd
     * @coversNothing
     */
    public function testRasterize_object_viewBox()
    {
        $obj = new SVGDocumentFragment('100%', '100%');
        $obj->setAttribute('viewBox', '100 100 2 4');

        $rect = new \SVG\Nodes\Shapes\SVGRect('100.5px', '100.5px', '1px', '3px');
        $rect->setStyle('fill', '#FF0000');
        $obj->addChild($rect);

        $rasterizer = new \SVG\Rasterization\SVGRasterizer(
            $obj->getWidth(),   // doc width
            $obj->getHeight(),  // doc height
            $obj->getViewBox(), // viewBox
            20,                 // result width
            40                  // result height
        );
        $obj->rasterize($rasterizer);
        $img = $rasterizer->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/rect-20x40.png'));
    }
}
