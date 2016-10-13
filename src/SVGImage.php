<?php

namespace JangoBrick\SVG;

use JangoBrick\SVG\Nodes\Structures\SVGDocumentFragment;
use JangoBrick\SVG\Rasterization\SVGRasterizer;
use JangoBrick\SVG\Reading\SVGReader;
use JangoBrick\SVG\Writing\SVGWriter;

class SVGImage
{
    private static $reader;

    private $document;

    public function __construct($width, $height, array $namespaces = array())
    {
        $this->document = new SVGDocumentFragment(true, $width, $height, $namespaces);
    }

    /**
     * @return SVGDocumentFragment
     */
    public function getDocument()
    {
        return $this->document;
    }

    public function toRasterImage($width, $height)
    {
        $docWidth  = $this->document->getWidth();
        $docHeight = $this->document->getHeight();

        $rasterizer = new SVGRasterizer($docWidth, $docHeight, $width, $height);
        $this->document->rasterize($rasterizer);

        return $rasterizer->getImage();
    }

    public function __toString()
    {
        return $this->toXMLString();
    }

    public function toXMLString()
    {
        $writer = new SVGWriter();
        $writer->writeNode($this->document);

        return $writer->getString();
    }

    public static function fromString($string)
    {
        return self::getReader()->parseString($string);
    }

    public static function fromFile($file)
    {
        return self::getReader()->parseFile($file);
    }

    private static function getReader()
    {
        if (!isset(self::$reader)) {
            self::$reader = new SVGReader();
        }
        return self::$reader;
    }
}
