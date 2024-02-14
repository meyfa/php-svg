<?php

namespace SVG\Reading;

use SVG\Nodes\SVGNode;
use SVG\Nodes\SVGGenericNodeType;

/**
 * This class contains a list of all known SVG node types, and enables dynamic
 * instantiation of the respective class.
 */
class NodeRegistry
{
    /**
    * @var string[] $nodeTypes Map of tag names to fully-qualified class names.
    */
    private static $nodeTypes = [
        'foreignObject'         => \SVG\Nodes\Embedded\SVGForeignObject::class,
        'image'                 => \SVG\Nodes\Embedded\SVGImage::class,

        'feBlend'               => \SVG\Nodes\Filters\SVGFEBlend::class,
        'feColorMatrix'         => \SVG\Nodes\Filters\SVGFEColorMatrix::class,
        'feComponentTransfer'   => \SVG\Nodes\Filters\SVGFEComponentTransfer::class,
        'feComposite'           => \SVG\Nodes\Filters\SVGFEComposite::class,
        'feConvolveMatrix'      => \SVG\Nodes\Filters\SVGFEConvolveMatrix::class,
        'feDiffuseLighting'     => \SVG\Nodes\Filters\SVGFEDiffuseLighting::class,
        'feDisplacementMap'     => \SVG\Nodes\Filters\SVGFEDisplacementMap::class,
        'feDistantLight'        => \SVG\Nodes\Filters\SVGFEDistantLight::class,
        'feDropShadow'          => \SVG\Nodes\Filters\SVGFEDropShadow::class,
        'feFlood'               => \SVG\Nodes\Filters\SVGFEFlood::class,
        'feFuncA'               => \SVG\Nodes\Filters\SVGFEFuncA::class,
        'feFuncB'               => \SVG\Nodes\Filters\SVGFEFuncB::class,
        'feFuncG'               => \SVG\Nodes\Filters\SVGFEFuncG::class,
        'feFuncR'               => \SVG\Nodes\Filters\SVGFEFuncR::class,
        'feGaussianBlur'        => \SVG\Nodes\Filters\SVGFEGaussianBlur::class,
        'feImage'               => \SVG\Nodes\Filters\SVGFEImage::class,
        'feMerge'               => \SVG\Nodes\Filters\SVGFEMerge::class,
        'feMergeNode'           => \SVG\Nodes\Filters\SVGFEMergeNode::class,
        'feMorphology'          => \SVG\Nodes\Filters\SVGFEMorphology::class,
        'feOffset'              => \SVG\Nodes\Filters\SVGFEOffset::class,
        'fePointLight'          => \SVG\Nodes\Filters\SVGFEPointLight::class,
        'feSpecularLighting'    => \SVG\Nodes\Filters\SVGFESpecularLighting::class,
        'feSpotLight'           => \SVG\Nodes\Filters\SVGFESpotLight::class,
        'feTile'                => \SVG\Nodes\Filters\SVGFETile::class,
        'feTurbulence'          => \SVG\Nodes\Filters\SVGFETurbulence::class,
        'filter'                => \SVG\Nodes\Filters\SVGFilter::class,

        'animate'               => \SVG\Nodes\Presentation\SVGAnimate::class,
        'animateMotion'         => \SVG\Nodes\Presentation\SVGAnimateMotion::class,
        'animateTransform'      => \SVG\Nodes\Presentation\SVGAnimateTransform::class,
        'linearGradient'        => \SVG\Nodes\Presentation\SVGLinearGradient::class,
        'mpath'                 => \SVG\Nodes\Presentation\SVGMPath::class,
        'radialGradient'        => \SVG\Nodes\Presentation\SVGRadialGradient::class,
        'set'                   => \SVG\Nodes\Presentation\SVGSet::class,
        'stop'                  => \SVG\Nodes\Presentation\SVGStop::class,
        'view'                  => \SVG\Nodes\Presentation\SVGView::class,

        'circle'                => \SVG\Nodes\Shapes\SVGCircle::class,
        'ellipse'               => \SVG\Nodes\Shapes\SVGEllipse::class,
        'line'                  => \SVG\Nodes\Shapes\SVGLine::class,
        'path'                  => \SVG\Nodes\Shapes\SVGPath::class,
        'polygon'               => \SVG\Nodes\Shapes\SVGPolygon::class,
        'polyline'              => \SVG\Nodes\Shapes\SVGPolyline::class,
        'rect'                  => \SVG\Nodes\Shapes\SVGRect::class,

        'clipPath'              => \SVG\Nodes\Structures\SVGClipPath::class,
        'defs'                  => \SVG\Nodes\Structures\SVGDefs::class,
        'svg'                   => \SVG\Nodes\Structures\SVGDocumentFragment::class,
        'g'                     => \SVG\Nodes\Structures\SVGGroup::class,
        'a'                     => \SVG\Nodes\Structures\SVGLinkGroup::class,
        'marker'                => \SVG\Nodes\Structures\SVGMarker::class,
        'mask'                  => \SVG\Nodes\Structures\SVGMask::class,
        'metadata'              => \SVG\Nodes\Structures\SVGMetadata::class,
        'pattern'               => \SVG\Nodes\Structures\SVGPattern::class,
        'script'                => \SVG\Nodes\Structures\SVGScript::class,
        'style'                 => \SVG\Nodes\Structures\SVGStyle::class,
        'switch'                => \SVG\Nodes\Structures\SVGSwitch::class,
        'symbol'                => \SVG\Nodes\Structures\SVGSymbol::class,
        'use'                   => \SVG\Nodes\Structures\SVGUse::class,

        'desc'                  => \SVG\Nodes\Texts\SVGDesc::class,
        'text'                  => \SVG\Nodes\Texts\SVGText::class,
        'textPath'              => \SVG\Nodes\Texts\SVGTextPath::class,
        'title'                 => \SVG\Nodes\Texts\SVGTitle::class,
        'tspan'                 => \SVG\Nodes\Texts\SVGTSpan::class,
    ];

    /**
     * Instantiate a node class matching the given type.
     * If no such class exists, a generic one will be used.
     *
     * @param string $type The node tag name ('svg', 'rect', 'title', etc.).
     *
     * @return SVGNode The node that was created.
     */
    public static function create(string $type): SVGNode
    {
        if (isset(self::$nodeTypes[$type])) {
            $nodeClass = self::$nodeTypes[$type];
            return new $nodeClass();
        }

        return new SVGGenericNodeType($type);
    }
}
