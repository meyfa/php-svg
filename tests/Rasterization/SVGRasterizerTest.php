<?php

namespace SVG;

use AssertGD\GDSimilarityConstraint;
use Exception;
use SVG\Rasterization\SVGRasterizer;

/**
 * @requires extension gd
 * @coversDefaultClass \SVG\Rasterization\SVGRasterizer
 * @covers ::<!public>
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGRasterizerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers ::getDocumentWidth
     */
    public function testGetDocumentWidth()
    {
        // should return parsed unit relative to target size
        $obj = new SVGRasterizer('50%', '50%', null, 100, 200);
        $this->assertEquals(50, $obj->getDocumentWidth());
        imagedestroy($obj->getImage());

        // should use '100%' by default
        $obj = new SVGRasterizer(null, null, null, 100, 200);
        $this->assertEquals(100, $obj->getDocumentWidth());
        imagedestroy($obj->getImage());
    }

    /**
     * @covers ::getDocumentHeight
     */
    public function testGetDocumentHeight()
    {
        // should return parsed unit relative to target size
        $obj = new SVGRasterizer('50%', '50%', null, 100, 200);
        $this->assertEquals(100, $obj->getDocumentHeight());
        imagedestroy($obj->getImage());

        // should use '100%' by default
        $obj = new SVGRasterizer(null, null, null, 100, 200);
        $this->assertEquals(200, $obj->getDocumentHeight());
        imagedestroy($obj->getImage());
    }

    /**
     * @covers ::getNormalizedDiagonal
     */
    public function testGetNormalizedDiagonal()
    {
        // should return the correct length
        $obj = new SVGRasterizer('50%', '50%', null, 100, 200);
        $this->assertEqualsWithDelta(79.05, $obj->getNormalizedDiagonal(), 0.01);
        imagedestroy($obj->getImage());
    }

    /**
     * @covers ::getWidth
     */
    public function testGetWidth()
    {
        // should return the constructor parameter
        $obj = new SVGRasterizer(10, 20, null, 100, 200);
        $this->assertEquals(100, $obj->getWidth());
        imagedestroy($obj->getImage());
    }

    /**
     * @covers ::getHeight
     */
    public function testGetHeight()
    {
        // should return the constructor parameter
        $obj = new SVGRasterizer(10, 20, null, 100, 200);
        $this->assertEquals(200, $obj->getHeight());
        imagedestroy($obj->getImage());
    }

    /**
     * @covers ::getDiagonalScale
     */
    public function testGetDiagonalScale()
    {
        // should use viewBox dimension when available
        $obj = new SVGRasterizer(10, 20, [37, 42, 25, 100], 100, 200);
        $this->assertEqualsWithDelta(3.16, $obj->getDiagonalScale(), 0.01);
        imagedestroy($obj->getImage());

        // should use document dimension when viewBox unavailable
        $obj = new SVGRasterizer(10, 20, [], 100, 300);
        $this->assertEqualsWithDelta(12.74, $obj->getDiagonalScale(), 0.01);
        imagedestroy($obj->getImage());
    }


    /**
     * @covers ::getViewbox
     */
    public function testGetViewbox()
    {
        // should return the constructor parameter
        $obj = new SVGRasterizer(10, 20, [37, 42, 25, 100], 100, 200);
        $this->assertSame([37, 42, 25, 100], $obj->getViewBox());
        imagedestroy($obj->getImage());

        // should return null for empty viewBox
        $obj = new SVGRasterizer(10, 20, [], 100, 200);
        $this->assertNull($obj->getViewBox());
        imagedestroy($obj->getImage());
    }

    /**
     * @covers ::getImage
     */
    public function testGetImage()
    {
        $obj = new SVGRasterizer(10, 20, [], 100, 200);

        if (class_exists('\GdImage', false)) {
            // PHP >=8: should be an image object
            $this->assertInstanceOf('\GdImage', $obj->getImage());
        } else {
            // PHP <8: should be a gd resource
            $this->assertTrue(is_resource($obj->getImage()));
            $this->assertSame('gd', get_resource_type($obj->getImage()));
        }

        // should have correct width and height
        $this->assertSame(100, imagesx($obj->getImage()));
        $this->assertSame(200, imagesy($obj->getImage()));

        imagedestroy($obj->getImage());
    }

    /**
     * @covers ::getCurrentTransform
     */
    public function testGetCurrentTransform()
    {
        // should use viewBox dimension when available
        $obj = new SVGRasterizer(10, 20, [37, 42, 25, 80], 100, 160);
        $transform = $obj->getCurrentTransform();
        $x = 100;
        $y = 100;
        $transform->map($x, $y);
        $this->assertEquals([4 * 100 + 4 * -37, 2 * 100 + 2 * -42], [$x, $y]);
        imagedestroy($obj->getImage());

        // should use document dimension when viewBox unavailable
        $obj = new SVGRasterizer(10, 20, [], 100, 160);
        $transform = $obj->getCurrentTransform();
        $x = 100;
        $y = 100;
        $transform->map($x, $y);
        $this->assertEquals([1000, 800], [$x, $y]);
        imagedestroy($obj->getImage());
    }

    /**
     * @covers ::pushTransform
     * @covers ::popTransform
     */
    public function testTransformStack()
    {
        $obj = new SVGRasterizer(10, 20, [37, 42, 25, 80], 100, 160);

        // expect pop to be disallowed without prior push
        try {
            $obj->popTransform();
            $this->fail('popTransform() did not throw');
        } catch (Exception $expected) {
        }

        // push one
        $original = $obj->getCurrentTransform();
        $pushed = $obj->pushTransform();
        $this->assertNotSame($original, $pushed);
        $this->assertSame($pushed, $obj->getCurrentTransform());

        // push another
        $pushed2 = $obj->pushTransform();
        $this->assertNotSame($original, $pushed2);
        $this->assertNotSame($pushed, $pushed2);
        $this->assertSame($pushed2, $obj->getCurrentTransform());

        // expect the transform to still inherit the original mapping
        $x = 100;
        $y = 100;
        $pushed2->map($x, $y);
        $this->assertEquals([4 * 100 + 4 * -37, 2 * 100 + 2 * -42], [$x, $y]);

        // pop all previously pushed
        $obj->popTransform();
        $this->assertSame($pushed, $obj->getCurrentTransform());
        $obj->popTransform();
        $this->assertSame($original, $obj->getCurrentTransform());

        // cleanup
        imagedestroy($obj->getImage());
    }

    /**
     * @covers ::render
     */
    public function testRenderWithNoSuchRenderId()
    {
        $this->expectException('\InvalidArgumentException');

        $obj = new SVGRasterizer(10, 20, [], 100, 200);
        $mockChild = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $obj->render('invalid_render_id', ['option' => 'value'], $mockChild);
    }

    /**
     * @covers \SVG\Rasterization\SVGRasterizer
     */
    public function testShouldRenderBackgroundTransparent()
    {
        $obj = new SVGRasterizer(32, 32, [], 32, 32, null);
        $img = $obj->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/bg-transparent.png'));

        imagedestroy($img);
    }

    /**
     * @covers \SVG\Rasterization\SVGRasterizer
     */
    public function testShouldRenderBackgroundSolidWhite()
    {
        $obj = new SVGRasterizer(32, 32, [], 32, 32, "#FFFFFF");
        $img = $obj->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/bg-white.png'));

        imagedestroy($img);
    }

    /**
     * @covers \SVG\Rasterization\SVGRasterizer
     */
    public function testShouldRenderBackgroundWhiteSemitransparent()
    {
        $obj = new SVGRasterizer(32, 32, [], 32, 32, "rgba(255,255,255,.5)");
        $img = $obj->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/bg-white-semitransparent.png'));

        imagedestroy($img);
    }
}
