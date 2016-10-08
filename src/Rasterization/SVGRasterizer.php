<?php

namespace JangoBrick\SVG\Rasterization;

use JangoBrick\SVG\Nodes\SVGNode;

class SVGRasterizer
{
    private static $renderers;

    private $docWidth, $docHeight;
    private $width, $height;
    private $scaleX, $scaleY;
    private $outImage;

    public function __construct($docWidth, $docHeight, $width, $height)
    {
        $this->docWidth  = $docWidth;
        $this->docHeight = $docHeight;

        $this->width  = $width;
        $this->height = $height;

        $this->scaleX = $width / $docWidth;
        $this->scaleY = $height / $docHeight;

        $this->outImage = self::createImage($width, $height);

        self::createDependencies();
    }

    private static function createImage($width, $height)
    {
        $img = imagecreatetruecolor($width, $height);

        imagealphablending($img, true);
        imagesavealpha($img, true);

        imagefill($img, 0, 0, 0x7F000000);

        return $img;
    }



    private static function createDependencies()
    {
        if (isset(self::$renderers)) {
            return;
        }

        self::$renderers = array(
            'rect'      => new Renderers\SVGRectRenderer(),
            'line'      => new Renderers\SVGLineRenderer(),
            'ellipse'   => new Renderers\SVGEllipseRenderer(),
        );
    }

    private static function getRenderer($id)
    {
        if (!isset(self::$renderers[$id])) {
            throw new \InvalidArgumentException("no such renderer: ".$id);
        }
        return self::$renderers[$id];
    }



    public function render($rendererId, array $params, SVGNode $context)
    {
        return (self::getRenderer($rendererId))
            ->render($this, $params, $context);
    }



    public function getDocumentWidth()
    {
        return $this->docWidth;
    }

    public function getDocumentHeight()
    {
        return $this->docHeight;
    }



    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }



    public function getScaleX()
    {
        return $this->scaleX;
    }

    public function getScaleY()
    {
        return $this->scaleY;
    }



    public function getImage()
    {
        return $this->outImage;
    }
}
