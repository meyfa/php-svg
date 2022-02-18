<?php

namespace SVG;

use AssertGD\GDSimilarityConstraint;
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
     * @covers ::getPathParser
     */
    public function testGetPathParser()
    {
        // should return an instance of PathParser
        $obj = new SVGRasterizer(10, 20, null, 100, 200);
        $this->assertInstanceOf('\SVG\Rasterization\Path\PathParser', $obj->getPathParser());
        imagedestroy($obj->getImage());
    }

    /**
     * @covers ::getPathApproximator
     */
    public function testGetPathApproximator()
    {
        // should return an instance of PathApproximator
        $obj = new SVGRasterizer(10, 20, null, 100, 200);
        $this->assertInstanceOf('\SVG\Rasterization\Path\PathApproximator', $obj->getPathApproximator());
        imagedestroy($obj->getImage());
    }

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
     * @covers ::getScaleX
     */
    public function testGetScaleX()
    {
        // should use viewBox dimension when available
        $obj = new SVGRasterizer(10, 20, array(37, 42, 25, 100), 100, 200);
        $this->assertEquals(4, $obj->getScaleX());
        imagedestroy($obj->getImage());

        // should use document dimension when viewBox unavailable
        $obj = new SVGRasterizer(10, 20, array(), 100, 200);
        $this->assertEquals(10, $obj->getScaleX());
        imagedestroy($obj->getImage());
    }

    /**
     * @covers ::getScaleY
     */
    public function testGetScaleY()
    {
        // should use viewBox dimension when available
        $obj = new SVGRasterizer(10, 20, array(37, 42, 25, 100), 100, 200);
        $this->assertEquals(2, $obj->getScaleY());
        imagedestroy($obj->getImage());

        // should use document dimension when viewBox unavailable
        $obj = new SVGRasterizer(10, 20, array(), 100, 200);
        $this->assertEquals(10, $obj->getScaleY());
        imagedestroy($obj->getImage());
    }

    /**
     * @covers ::getOffsetX
     */
    public function testGetOffsetX()
    {
        // should return scaled viewBox offset when available
        $obj = new SVGRasterizer(10, 20, array(37, 42, 25, 100), 100, 200);
        $this->assertEquals(-37 * $obj->getScaleX(), $obj->getOffsetX());
        imagedestroy($obj->getImage());

        // should return 0 when viewBox unavailable
        $obj = new SVGRasterizer(10, 20, array(), 100, 200);
        $this->assertEquals(0, $obj->getOffsetX());
        imagedestroy($obj->getImage());
    }

    /**
     * @covers ::getOffsetY
     */
    public function testGetOffsetY()
    {
        // should return scaled viewBox offset when available
        $obj = new SVGRasterizer(10, 20, array(37, 42, 25, 100), 100, 200);
        $this->assertEquals(-42 * $obj->getScaleY(), $obj->getOffsetY());
        imagedestroy($obj->getImage());

        // should return 0 when viewBox unavailable
        $obj = new SVGRasterizer(10, 20, array(), 100, 200);
        $this->assertEquals(0, $obj->getOffsetY());
        imagedestroy($obj->getImage());
    }

    /**
     * @covers ::getViewbox
     */
    public function testGetViewbox()
    {
        // should return the constructor parameter
        $obj = new SVGRasterizer(10, 20, array(37, 42, 25, 100), 100, 200);
        $this->assertSame(array(37, 42, 25, 100), $obj->getViewBox());
        imagedestroy($obj->getImage());

        // should return null for empty viewBox
        $obj = new SVGRasterizer(10, 20, array(), 100, 200);
        $this->assertNull($obj->getViewBox());
        imagedestroy($obj->getImage());
    }

    /**
     * @covers ::getImage
     */
    public function testGetImage()
    {
        $obj = new SVGRasterizer(10, 20, array(), 100, 200);

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
     * @covers ::render
     */
    public function testRenderWithNoSuchRenderId()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new SVGRasterizer(10, 20, array(), 100, 200);
        $mockChild = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $obj->render('invalid_render_id', array('option' => 'value'), $mockChild);
    }

    /**
     * @covers \SVG\Rasterization\SVGRasterizer
     */
    public function testShouldRenderBackgroundTransparent()
    {
        $obj = new SVGRasterizer(32, 32, array(), 32, 32, null);
        $img = $obj->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/bg-transparent.png'));

        imagedestroy($img);
    }

    /**
     * @covers \SVG\Rasterization\SVGRasterizer
     */
    public function testShouldRenderBackgroundSolidWhite()
    {
        $obj = new SVGRasterizer(32, 32, array(), 32, 32, "#FFFFFF");
        $img = $obj->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/bg-white.png'));

        imagedestroy($img);
    }

    /**
     * @covers \SVG\Rasterization\SVGRasterizer
     */
    public function testShouldRenderBackgroundWhiteSemitransparent()
    {
        $obj = new SVGRasterizer(32, 32, array(), 32, 32, "rgba(255,255,255,.5)");
        $img = $obj->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/bg-white-semitransparent.png'));

        imagedestroy($img);
    }
}
