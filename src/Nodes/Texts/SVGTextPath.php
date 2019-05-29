<?php

namespace SVG\Nodes\Texts;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'textPath'.
 */
class SVGTextPath extends SVGNodeContainer
{
    const TAG_NAME = 'textPath';
    
    public function __construct($text = '', $href = '')
	{
		parent::__construct();
		$this->setValue($text);

		$this->setAttribute('xlink:href', $href);

	}

	public function rasterize(SVGRasterizer $rasterizer)
	{
		if (empty($this->font)) {
			return;
		}

		$rasterizer->render('textPath', array(
			'xlink:href' => $this->getAttribute('xlink:href'),
			'text'       => $this->getValue()
		), $this);
	}
}
