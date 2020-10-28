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
    private static $nodeTypes = array(
        'foreignObject'         => 'SVG\Nodes\Embedded\SVGForeignObject',
        'image'                 => 'SVG\Nodes\Embedded\SVGImage',

        'feBlend'               => 'SVG\Nodes\Filters\SVGFEBlend',
        'feColorMatrix'         => 'SVG\Nodes\Filters\SVGFEColorMatrix',
        'feComponentTransfer'   => 'SVG\Nodes\Filters\SVGFEComponentTransfer',
        'feComposite'           => 'SVG\Nodes\Filters\SVGFEComposite',
        'feConvolveMatrix'      => 'SVG\Nodes\Filters\SVGFEConvolveMatrix',
        'feDiffuseLighting'     => 'SVG\Nodes\Filters\SVGFEDiffuseLighting',
        'feDisplacementMap'     => 'SVG\Nodes\Filters\SVGFEDisplacementMap',
        'feDistantLight'        => 'SVG\Nodes\Filters\SVGFEDistantLight',
        'feDropShadow'          => 'SVG\Nodes\Filters\SVGFEDropShadow',
        'feFlood'               => 'SVG\Nodes\Filters\SVGFEFlood',
        'feFuncA'               => 'SVG\Nodes\Filters\SVGFEFuncA',
        'feFuncB'               => 'SVG\Nodes\Filters\SVGFEFuncB',
        'feFuncG'               => 'SVG\Nodes\Filters\SVGFEFuncG',
        'feFuncR'               => 'SVG\Nodes\Filters\SVGFEFuncR',
        'feGaussianBlur'        => 'SVG\Nodes\Filters\SVGFEGaussianBlur',
        'feImage'               => 'SVG\Nodes\Filters\SVGFEImage',
        'feMerge'               => 'SVG\Nodes\Filters\SVGFEMerge',
        'feMergeNode'           => 'SVG\Nodes\Filters\SVGFEMergeNode',
        'feMorphology'          => 'SVG\Nodes\Filters\SVGFEMorphology',
        'feOffset'              => 'SVG\Nodes\Filters\SVGFEOffset',
        'fePointLight'          => 'SVG\Nodes\Filters\SVGFEPointLight',
        'feSpecularLighting'    => 'SVG\Nodes\Filters\SVGFESpecularLighting',
        'feSpotLight'           => 'SVG\Nodes\Filters\SVGFESpotLight',
        'feTile'                => 'SVG\Nodes\Filters\SVGFETile',
        'feTurbulence'          => 'SVG\Nodes\Filters\SVGFETurbulence',
        'filter'                => 'SVG\Nodes\Filters\SVGFilter',

        'animate'               => 'SVG\Nodes\Presentation\SVGAnimate',
        'animateMotion'         => 'SVG\Nodes\Presentation\SVGAnimateMotion',
        'animateTransform'      => 'SVG\Nodes\Presentation\SVGAnimateTransform',
        'linearGradient'        => 'SVG\Nodes\Presentation\SVGLinearGradient',
        'mpath'                 => 'SVG\Nodes\Presentation\SVGMPath',
        'radialGradient'        => 'SVG\Nodes\Presentation\SVGRadialGradient',
        'set'                   => 'SVG\Nodes\Presentation\SVGSet',
        'stop'                  => 'SVG\Nodes\Presentation\SVGStop',
        'view'                  => 'SVG\Nodes\Presentation\SVGView',

        'circle'                => 'SVG\Nodes\Shapes\SVGCircle',
        'ellipse'               => 'SVG\Nodes\Shapes\SVGEllipse',
        'line'                  => 'SVG\Nodes\Shapes\SVGLine',
        'path'                  => 'SVG\Nodes\Shapes\SVGPath',
        'polygon'               => 'SVG\Nodes\Shapes\SVGPolygon',
        'polyline'              => 'SVG\Nodes\Shapes\SVGPolyline',
        'rect'                  => 'SVG\Nodes\Shapes\SVGRect',

        'clipPath'              => 'SVG\Nodes\Structures\SVGClipPath',
        'defs'                  => 'SVG\Nodes\Structures\SVGDefs',
        'svg'                   => 'SVG\Nodes\Structures\SVGDocumentFragment',
        'g'                     => 'SVG\Nodes\Structures\SVGGroup',
        'a'                     => 'SVG\Nodes\Structures\SVGLinkGroup',
        'marker'                => 'SVG\Nodes\Structures\SVGMarker',
        'mask'                  => 'SVG\Nodes\Structures\SVGMask',
        'metadata'              => 'SVG\Nodes\Structures\SVGMetadata',
        'pattern'               => 'SVG\Nodes\Structures\SVGPattern',
        'script'                => 'SVG\Nodes\Structures\SVGScript',
        'style'                 => 'SVG\Nodes\Structures\SVGStyle',
        'switch'                => 'SVG\Nodes\Structures\SVGSwitch',
        'symbol'                => 'SVG\Nodes\Structures\SVGSymbol',
        'use'                   => 'SVG\Nodes\Structures\SVGUse',

        'desc'                  => 'SVG\Nodes\Texts\SVGDesc',
        'text'                  => 'SVG\Nodes\Texts\SVGText',
        'textPath'              => 'SVG\Nodes\Texts\SVGTextPath',
        'title'                 => 'SVG\Nodes\Texts\SVGTitle',
        'tspan'                 => 'SVG\Nodes\Texts\SVGTSpan',
    );

    /**
     * Instantiate a node class matching the given type.
     * If no such class exists, a generic one will be used.
     *
     * @param string $type The node tag name ('svg', 'rect', 'title', etc.).
     *
     * @return SVGNode The node that was created.
     */
    public static function create($type)
    {
        if (isset(self::$nodeTypes[$type])) {
            $nodeClass = self::$nodeTypes[$type];
            return new $nodeClass();
        }

        return new SVGGenericNodeType($type);
    }
}
