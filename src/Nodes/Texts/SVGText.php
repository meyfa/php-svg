<?php

namespace SVG\Nodes\Texts;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;
use SVG\Rasterization\Transform\TransformParser;
use SVG\Utilities\Units\Length;

/**
 * Represents the SVG tag 'text'.
 *
 * Usage example:
 *
 * \SVG\SVG::addFont('./fonts/Ubuntu-Regular.ttf');
 *
 * $svg = new \SVG\SVG(600, 400);
 *
 * $svg->getDocument()->addChild(
 *   (new \SVG\Nodes\Texts\SVGText('hello', 50, 50))
 *     ->setFontFamily('Ubuntu')
 *     ->setFontSize(15)
 * );
 *
 */
class SVGText extends SVGNodeContainer
{
    const TAG_NAME = 'text';

    public function __construct(string $text = '', $x = 0, $y = 0)
    {
        parent::__construct();
        $this->setValue($text);

        $this->setAttribute('x', $x);
        $this->setAttribute('y', $y);
    }

    /**
     * Set the CSS font-family property.
     *
     * @param string $fontFamily The value for the CSS font-family property.
     * @return $this
     */
    public function setFontFamily(string $fontFamily): SVGText
    {
        $this->setStyle('font-family', $fontFamily);
        return $this;
    }

    /**
     * Set the CSS font-size property.
     *
     * @param $fontSize mixed The value for the CSS font-size property.
     * @return $this
     */
    public function setFontSize($fontSize): SVGText
    {
        $this->setStyle('font-size', $fontSize);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getComputedStyle(string $name): ?string
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
    public function rasterize(SVGRasterizer $rasterizer): void
    {
        TransformParser::parseTransformString($this->getAttribute('transform'), $rasterizer->pushTransform());

        // TODO: support percentage font sizes
        //       https://www.w3.org/TR/SVG11/text.html#FontSizeProperty
        //       "Percentages: refer to parent element's font size"
        // For now, assume the standard font size of 16px as reference size
        // Default to 16px if font size could not be parsed
        $fontSize = Length::convert($this->getComputedStyle('font-size'), 16) ?? 16;

        $rasterizer->render('text', [
            'x'          => Length::convert($this->getAttribute('x'), $rasterizer->getDocumentWidth()),
            'y'          => Length::convert($this->getAttribute('y'), $rasterizer->getDocumentHeight()),
            'fontFamily' => $this->getComputedStyle('font-family'),
            'fontWeight' => $this->getComputedStyle('font-weight'),
            'fontStyle'  => $this->getComputedStyle('font-style'),
            'fontSize'   => $fontSize,
            'anchor'     => $this->getComputedStyle('text-anchor'),
            'text'       => $this->getValue(),
        ], $this);

        $rasterizer->popTransform();
    }
}
