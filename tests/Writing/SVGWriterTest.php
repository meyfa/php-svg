<?php

use SVG\Writing\SVGWriter;

/**
 * @SuppressWarnings(PHPMD)
 */
class SVGWriterTest extends PHPUnit_Framework_TestCase
{
    // THE TESTS IN THIS CLASS DO NOT ADHERE TO THE STANDARD LAYOUT
    // OF TESTING ONE CLASS METHOD PER TEST METHOD
    // BECAUSE THE CLASS UNDER TEST IS A SINGLE-FEATURE CLASS

    private $xmlDeclaration = '<?xml version="1.0" encoding="utf-8"?>';

    public function testShouldIncludeXMLDeclaration()
    {
        // should start with the XML declaration
        $obj = new SVGWriter();
        $this->assertEquals($this->xmlDeclaration, $obj->getString());
    }

    public function testShouldWriteTags()
    {
        // should write opening and closing tags for containers
        $obj = new SVGWriter();
        $node = new \SVG\Nodes\Structures\SVGGroup();
        $obj->writeNode($node);
        $expect = $this->xmlDeclaration.'<g></g>';
        $this->assertEquals($expect, $obj->getString());

        // should write self-closing tag for non-containers
        $obj = new SVGWriter();
        $node = new \SVG\Nodes\Shapes\SVGRect();
        $obj->writeNode($node);
        $expect = $this->xmlDeclaration.'<rect />';
        $this->assertEquals($expect, $obj->getString());
    }

    public function testShouldWriteAttributes()
    {
        // should write attributes for containers
        $obj = new SVGWriter();
        $node = new \SVG\Nodes\Structures\SVGGroup();
        $node->setAttribute('id', 'testg');
        $obj->writeNode($node);
        $expect = $this->xmlDeclaration.'<g id="testg"></g>';
        $this->assertEquals($expect, $obj->getString());
    }

    public function testShouldWriteStyles()
    {
        // should serialize styles correctly
        $obj = new SVGWriter();
        $node = new \SVG\Nodes\Structures\SVGGroup();
        $node->setStyle('fill', '#ABC')->setStyle('opacity', '.5');
        $obj->writeNode($node);
        $expect = $this->xmlDeclaration.'<g style="fill: #ABC; opacity: .5"></g>';
        $this->assertEquals($expect, $obj->getString());
    }

    public function testShouldWriteChildren()
    {
        // should write children
        $obj = new SVGWriter();
        $node = new \SVG\Nodes\Structures\SVGGroup();
        $node->addChild(
            (new \SVG\Nodes\Structures\SVGGroup())
                ->addChild(new \SVG\Nodes\Shapes\SVGRect())
        );
        $obj->writeNode($node);
        $expect = $this->xmlDeclaration.'<g><g><rect /></g></g>';
        $this->assertEquals($expect, $obj->getString());
    }

    public function testShouldWriteStyleTagInCDATA()
    {
        // should enclose style tag content in <![CDATA[...]]>
        $obj = new SVGWriter();
        $node = new \SVG\Nodes\Structures\SVGStyle('g {display: none;}');
        $obj->writeNode($node);
        $expect = $this->xmlDeclaration.'<style type="text/css">'.
            '<![CDATA[g {display: none;}]]></style>';
        $this->assertEquals($expect, $obj->getString());
    }

    public function testShouldEncodeEntities()
    {
        // should encode entities in attributes
        $obj = new SVGWriter();
        $obj->writeNode(
            (new \SVG\Nodes\Structures\SVGGroup())
                ->setAttribute('id', '" foo&bar>')
                ->setStyle('content', '" foo&bar>')
        );
        $expect = $this->xmlDeclaration.'<g id="&quot; foo&amp;bar&gt;" '.
            'style="content: &quot; foo&amp;bar&gt;"></g>';
        $this->assertEquals($expect, $obj->getString());

        // should encode entities in style body
        $obj = new SVGWriter();
        $obj->writeNode(new \SVG\Nodes\Structures\SVGStyle('" foo&bar>'));
        $expect = $this->xmlDeclaration.'<style type="text/css">'.
            '<![CDATA[&quot; foo&amp;bar&gt;]]></style>';
        $this->assertEquals($expect, $obj->getString());
    }
}
