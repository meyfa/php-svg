<?php

namespace SVG\Reading;

use SVG\Nodes\Embedded\SVGForeignObject;
use SVG\Nodes\Embedded\SVGImage;
use SVG\Nodes\Filters\SVGFEBlend;
use SVG\Nodes\Filters\SVGFEColorMatrix;
use SVG\Nodes\Filters\SVGFEComponentTransfer;
use SVG\Nodes\Filters\SVGFEComposite;
use SVG\Nodes\Filters\SVGFEConvolveMatrix;
use SVG\Nodes\Filters\SVGFEDiffuseLighting;
use SVG\Nodes\Filters\SVGFEDisplacementMap;
use SVG\Nodes\Filters\SVGFEDistantLight;
use SVG\Nodes\Filters\SVGFEDropShadow;
use SVG\Nodes\Filters\SVGFEFlood;
use SVG\Nodes\Filters\SVGFEFuncA;
use SVG\Nodes\Filters\SVGFEFuncB;
use SVG\Nodes\Filters\SVGFEFuncG;
use SVG\Nodes\Filters\SVGFEFuncR;
use SVG\Nodes\Filters\SVGFEGaussianBlur;
use SVG\Nodes\Filters\SVGFEImage;
use SVG\Nodes\Filters\SVGFEMerge;
use SVG\Nodes\Filters\SVGFEMergeNode;
use SVG\Nodes\Filters\SVGFEMorphology;
use SVG\Nodes\Filters\SVGFEOffset;
use SVG\Nodes\Filters\SVGFEPointLight;
use SVG\Nodes\Filters\SVGFESpecularLighting;
use SVG\Nodes\Filters\SVGFESpotLight;
use SVG\Nodes\Filters\SVGFETile;
use SVG\Nodes\Filters\SVGFETurbulence;
use SVG\Nodes\Filters\SVGFilter;
use SVG\Nodes\Presentation\SVGAnimate;
use SVG\Nodes\Presentation\SVGAnimateMotion;
use SVG\Nodes\Presentation\SVGAnimateTransform;
use SVG\Nodes\Presentation\SVGLinearGradient;
use SVG\Nodes\Presentation\SVGMPath;
use SVG\Nodes\Presentation\SVGRadialGradient;
use SVG\Nodes\Presentation\SVGSet;
use SVG\Nodes\Presentation\SVGStop;
use SVG\Nodes\Presentation\SVGView;
use SVG\Nodes\Shapes\SVGCircle;
use SVG\Nodes\Shapes\SVGEllipse;
use SVG\Nodes\Shapes\SVGLine;
use SVG\Nodes\Shapes\SVGPath;
use SVG\Nodes\Shapes\SVGPolygon;
use SVG\Nodes\Shapes\SVGPolyline;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Structures\SVGClipPath;
use SVG\Nodes\Structures\SVGDefs;
use SVG\Nodes\Structures\SVGDocumentFragment;
use SVG\Nodes\Structures\SVGGroup;
use SVG\Nodes\Structures\SVGLinkGroup;
use SVG\Nodes\Structures\SVGMarker;
use SVG\Nodes\Structures\SVGMask;
use SVG\Nodes\Structures\SVGMetadata;
use SVG\Nodes\Structures\SVGPattern;
use SVG\Nodes\Structures\SVGScript;
use SVG\Nodes\Structures\SVGStyle;
use SVG\Nodes\Structures\SVGSwitch;
use SVG\Nodes\Structures\SVGSymbol;
use SVG\Nodes\Structures\SVGUse;
use SVG\Nodes\SVGNode;
use SVG\Nodes\SVGGenericNodeType;
use SVG\Nodes\Texts\SVGDesc;
use SVG\Nodes\Texts\SVGText;
use SVG\Nodes\Texts\SVGTextPath;
use SVG\Nodes\Texts\SVGTitle;
use SVG\Nodes\Texts\SVGTSpan;

/**
 * This class contains a list of all known SVG node types, and enables dynamic
 * instantiation of the respective class.
 */
class NodeRegistry
{
    /**
    * @var string[] $nodeTypes Map of tag names to fully-qualified class names.
    */
    private static array $nodeTypes = [
        'foreignObject'         => SVGForeignObject::class,
        'image'                 => SVGImage::class,

        'feBlend'               => SVGFEBlend::class,
        'feColorMatrix'         => SVGFEColorMatrix::class,
        'feComponentTransfer'   => SVGFEComponentTransfer::class,
        'feComposite'           => SVGFEComposite::class,
        'feConvolveMatrix'      => SVGFEConvolveMatrix::class,
        'feDiffuseLighting'     => SVGFEDiffuseLighting::class,
        'feDisplacementMap'     => SVGFEDisplacementMap::class,
        'feDistantLight'        => SVGFEDistantLight::class,
        'feDropShadow'          => SVGFEDropShadow::class,
        'feFlood'               => SVGFEFlood::class,
        'feFuncA'               => SVGFEFuncA::class,
        'feFuncB'               => SVGFEFuncB::class,
        'feFuncG'               => SVGFEFuncG::class,
        'feFuncR'               => SVGFEFuncR::class,
        'feGaussianBlur'        => SVGFEGaussianBlur::class,
        'feImage'               => SVGFEImage::class,
        'feMerge'               => SVGFEMerge::class,
        'feMergeNode'           => SVGFEMergeNode::class,
        'feMorphology'          => SVGFEMorphology::class,
        'feOffset'              => SVGFEOffset::class,
        'fePointLight'          => SVGFEPointLight::class,
        'feSpecularLighting'    => SVGFESpecularLighting::class,
        'feSpotLight'           => SVGFESpotLight::class,
        'feTile'                => SVGFETile::class,
        'feTurbulence'          => SVGFETurbulence::class,
        'filter'                => SVGFilter::class,

        'animate'               => SVGAnimate::class,
        'animateMotion'         => SVGAnimateMotion::class,
        'animateTransform'      => SVGAnimateTransform::class,
        'linearGradient'        => SVGLinearGradient::class,
        'mpath'                 => SVGMPath::class,
        'radialGradient'        => SVGRadialGradient::class,
        'set'                   => SVGSet::class,
        'stop'                  => SVGStop::class,
        'view'                  => SVGView::class,

        'circle'                => SVGCircle::class,
        'ellipse'               => SVGEllipse::class,
        'line'                  => SVGLine::class,
        'path'                  => SVGPath::class,
        'polygon'               => SVGPolygon::class,
        'polyline'              => SVGPolyline::class,
        'rect'                  => SVGRect::class,

        'clipPath'              => SVGClipPath::class,
        'defs'                  => SVGDefs::class,
        'svg'                   => SVGDocumentFragment::class,
        'g'                     => SVGGroup::class,
        'a'                     => SVGLinkGroup::class,
        'marker'                => SVGMarker::class,
        'mask'                  => SVGMask::class,
        'metadata'              => SVGMetadata::class,
        'pattern'               => SVGPattern::class,
        'script'                => SVGScript::class,
        'style'                 => SVGStyle::class,
        'switch'                => SVGSwitch::class,
        'symbol'                => SVGSymbol::class,
        'use'                   => SVGUse::class,

        'desc'                  => SVGDesc::class,
        'text'                  => SVGText::class,
        'textPath'              => SVGTextPath::class,
        'title'                 => SVGTitle::class,
        'tspan'                 => SVGTSpan::class,
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
