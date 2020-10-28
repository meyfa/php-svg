<?php

namespace SVG;

use SVG\Reading\NodeRegistry;

/**
 * @covers \SVG\Reading\NodeRegistry
 *
 * @SuppressWarnings(PHPMD)
 */
class NodeRegistryTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldConstructKnownTypes()
    {
        $result = NodeRegistry::create('rect');
        $this->assertInstanceOf('SVG\Nodes\Shapes\SVGRect', $result);
    }

    public function testShouldUseGenericTypeForOthers()
    {
        $result = NodeRegistry::create('div');
        $this->assertInstanceOf('SVG\Nodes\SVGGenericNodeType', $result);
    }
}
