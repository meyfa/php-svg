<?php

namespace SVG\Reading;

use SimpleXMLElement;
use SVG\SVG;
use SVG\Nodes\SVGNode;
use SVG\Nodes\SVGNodeContainer;
use SVG\Nodes\SVGGenericNodeType;
use SVG\Utilities\SVGStyleParser;

/**
 * This class is used to read XML strings or files and turn them into instances
 * of SVG by parsing the document tree.
 *
 * In contrast to SVGWriter, a single instance can perform any number of reads.
 */
class SVGReader
{
    /**
     * Parses the given string as XML and turns it into an instance of SVG.
     * Returns null when parsing fails.
     *
     * @param string $string The XML string to parse.
     *
     * @return SVG|null An image object representing the parse result.
     */
    public function parseString($string)
    {
        $xml = simplexml_load_string($string);
        return $this->parseXML($xml);
    }

    /**
     * Parses the file at the given path/URL as XML and turns it into an
     * instance of SVG.
     *
     * The path can be on the local file system, or a URL on the network.
     * Returns null when parsing fails.
     *
     * @param string $filename The path or URL of the file to parse.
     *
     * @return SVG|null An image object representing the parse result.
     */
    public function parseFile($filename)
    {
        $xml = simplexml_load_file($filename);
        return $this->parseXML($xml);
    }

    /**
     * Parses the given XML document into an instance of SVG.
     * Returns null when parsing fails.
     *
     * @param SimpleXMLElement $xml The root node of the SVG document to parse.
     *
     * @return SVG|null An image object representing the parse result.
     */
    public function parseXML(SimpleXMLElement $xml)
    {
        $name = $xml->getName();
        if ($name !== 'svg') {
            return null;
        }

        $img = new SVG();
        $doc = $img->getDocument();

        $namespaces = $xml->getNamespaces(true);
        $doc->setNamespaces($namespaces);

        $nsKeys = array_keys($namespaces);
        if (!in_array('', $nsKeys, true) && !in_array(null, $nsKeys, true)) {
            $nsKeys[] = '';
        }

        $this->applyAttributes($doc, $xml, $nsKeys);
        $this->applyStyles($doc, $xml);
        $this->addChildren($doc, $xml, $nsKeys);

        return $img;
    }

    /**
     * Iterates over all XML attributes and applies them to the given node.
     *
     * Since styles in SVG can also be expressed with attributes, this method
     * checks the name of each attribute and, if it matches that of a style,
     * applies it as a style instead. The actual 'style' attribute is ignored.
     *
     * @see SVGReader::$styleAttributes The attributes considered styles.
     *
     * @param SVGNode           $node       The node to apply the attributes to.
     * @param SimpleXMLElement  $xml        The attribute source.
     * @param string[]          $namespaces Array of allowed namespace prefixes.
     *
     * @return void
     */
    private function applyAttributes(SVGNode $node, SimpleXMLElement $xml, array $namespaces)
    {
        foreach ($namespaces as $ns) {
            foreach ($xml->attributes($ns, true) as $key => $value) {
                if ($key === 'style') {
                    continue;
                }
                if (AttributeRegistry::isStyle($key)) {
                    $convertedValue = AttributeRegistry::convertStyleAttribute($key, $value);
                    $node->setStyle($key, $convertedValue);
                    continue;
                }
                if (!empty($ns) && $ns !== 'svg') {
                    $key = $ns . ':' . $key;
                }
                $node->setAttribute($key, $value);
            }
        }
    }

    /**
     * Parses the 'style' attribute (if it exists) and applies all styles to the
     * given node.
     *
     * This method does NOT handle styles expressed as attributes (stroke="").
     * @see SVGReader::applyAttributes() For styles expressed as attributes.
     *
     * @param SVGNode           $node The node to apply the styles to.
     * @param SimpleXMLElement  $xml  The attribute source.
     *
     * @return void
     */
    private function applyStyles(SVGNode $node, SimpleXMLElement $xml)
    {
        if (!isset($xml['style'])) {
            return;
        }

        $styles = SVGStyleParser::parseStyles($xml['style']);
        foreach ($styles as $key => $value) {
            $node->setStyle($key, $value);
        }
    }

    /**
     * Iterates over all children, parses them into library class instances,
     * and adds them to the given node container.
     *
     * @param SVGNodeContainer  $node       The node to add the children to.
     * @param SimpleXMLElement  $xml        The XML node containing the children.
     * @param string[]          $namespaces Array of allowed namespace prefixes.
     *
     * @return void
     */
    private function addChildren(SVGNodeContainer $node, SimpleXMLElement $xml, array $namespaces)
    {
        foreach ($namespaces as $ns) {
            foreach ($xml->children($ns, true) as $child) {
                $node->addChild($this->parseNode($ns, $child, $namespaces));
            }
        }
    }

    /**
     * Parses the given XML element into an instance of a SVGNode subclass.
     * Unknown node types use a generic implementation.
     *
     * @param string            $ns         The tag name namespace prefix.
     * @param SimpleXMLElement  $xml        The XML element to parse.
     * @param string[]          $namespaces Array of allowed namespace prefixes.
     *
     * @return SVGNode The parsed node.
     *
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     */
    private function parseNode($ns, SimpleXMLElement $xml, array $namespaces)
    {
        $tagName = $xml->getName();
        if (!empty($ns) && $ns !== 'svg') {
            $tagName = $ns . ':' . $tagName;
        }
        $node = NodeRegistry::create($tagName);

        // obtain array of namespaces that are declared directly on this node
        // TODO find solution for PHP < 5.4 (where the 2nd parameter was introduced)
        $extraNamespaces = @$xml->getDocNamespaces(false, false);
        if (!empty($extraNamespaces)) {
            $namespaces = array_unique(array_merge($namespaces, array_keys($extraNamespaces)));
            $node->setNamespaces($extraNamespaces);
        }

        $this->applyAttributes($node, $xml, $namespaces);
        $this->applyStyles($node, $xml);
        $node->setValue($xml);

        if ($node instanceof SVGNodeContainer) {
            $this->addChildren($node, $xml, $namespaces);
        }

        return $node;
    }
}
