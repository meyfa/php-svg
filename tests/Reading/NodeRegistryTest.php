<?php

namespace SVG;

use SimpleXMLElement;
use SVG\Reading\NodeRegistry;

/**
 * @SuppressWarnings(PHPMD)
 */
class NodeRegistryTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldConstructKnownTypes()
    {
        $xml = new SimpleXMLElement('<rect />');
        $result = NodeRegistry::create($xml);

        $this->assertInstanceOf('SVG\Nodes\Shapes\SVGRect', $result);
    }

    public function testShouldUseGenericTypeForOthers()
    {
        $xml = new SimpleXMLElement('<div />');
        $result = NodeRegistry::create($xml);

        $this->assertInstanceOf('SVG\Nodes\SVGGenericNodeType', $result);
    }
}
