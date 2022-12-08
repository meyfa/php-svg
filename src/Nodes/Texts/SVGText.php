<?php

namespace SVG\Nodes\Texts;

use SVG\Nodes\SVGNodeContainer;
use SVG\Nodes\Structures\SVGFont;
use SVG\Rasterization\SVGRasterizer;
use SVG\Rasterization\Transform\TransformParser;
use SVG\Utilities\Units\Length;

/**
 * Represents the SVG tag 'text'.
 *
 * Usage:
 *
 * $svg = new \SVG\SVG(600, 400);
 *
 * $font = new \SVG\Nodes\Structures\SVGFont('openGost', 'OpenGostTypeA-Regular.ttf');
 * $svg->getDocument()->addChild($font);
 *
 * $svg->getDocument()->addChild(
 *   (new \SVG\Nodes\Texts\SVGText('hello', 50, 50))
 *     ->setFont($font)
 *     ->setSize(15)
 *     ->setStyle('stroke', '#f00')
 *     ->setStyle('stroke-width', 1)
 * );
 *
 */
class SVGText extends SVGNodeContainer
{
    const TAG_NAME = 'text';

    /**
     * @var SVGFont
     */
    private $font;

    public function __construct($text = '', $x = 0, $y = 0)
    {
        parent::__construct();
        $this->setValue($text);

        $this->setAttribute('x', $x);
        $this->setAttribute('y', $y);
    }

    /**
     * Set font
     *
     * @param SVGFont $font
     * @return $this
     */
    public function setFont(SVGFont $font)
    {
        $this->font = $font;
        $this->setStyle('font-family', $font->getFontName());
        return $this;
    }

    /**
     * Set font size
     *
     * @param $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->setStyle('font-size', $size);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getComputedStyle($name)
    {
        // force stroke before fill
        if ($name === 'paint-order') {
            // TODO remove this workaround
            return 'stroke fill';
        }

        return parent::getComputedStyle($name);
    }

    /**
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer)
    {
        if (empty($this->font)) {
            return;
        }

        TransformParser::parseTransformString($this->getAttribute('transform'), $rasterizer->pushTransform());

        // TODO: support percentage font sizes
        //       https://www.w3.org/TR/SVG11/text.html#FontSizeProperty
        //       "Percentages: refer to parent element's font size"
        // For now, assume the standard font size of 16px as reference size
        $fontSize = Length::convert($this->getComputedStyle('font-size'), 16);

        $rasterizer->render('text', [
            'x'         => Length::convert($this->getAttribute('x'), $rasterizer->getDocumentWidth()),
            'y'         => Length::convert($this->getAttribute('y'), $rasterizer->getDocumentHeight()),
            'size'      => $fontSize,
            'anchor'    => $this->getComputedStyle('text-anchor'),
            'text'      => $this->getValue(),
            'font_path' => $this->font->getFontPath(),
        ], $this);

        $rasterizer->popTransform();
    }
}
