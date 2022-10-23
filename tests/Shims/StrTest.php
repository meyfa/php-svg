<?php

namespace SVG\Shims;

use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{
    /** @dataProvider provideTrimsWithDefaultCharacters */
    public function testTrimsWithDefaultCharacters($input, $expectedResult)
    {
        $result = Str::trim($input);
        $this->assertSame($expectedResult, $result);
    }

    /** @provides testTrimsWithDefaultCharacters */
    public function provideTrimsWithDefaultCharacters()
    {
        return array(
            "It trims null" => array(null, ""),
            "It trims empty string" => array("", ""),
            "It trims front" => array(" foo", "foo"),
            "It trims end" => array("bar ", "bar"),
            "It trims front and end" => array(" foobar ", 'foobar'),
            "It doesn't touch the middle" => array(" foo baz ", 'foo baz'),
            "It trims tabs" => array(" \t foo", 'foo'),
            "It trims carriage returns" => array("bar \r", 'bar'),
            "It trims NUL-byte" => array("foobar \0", 'foobar'),
            "It trims vertical tab" => array("foobaz \v", 'foobaz'),
        );
    }

    /** @dataProvider provideTrimsWithCustomCharacters */
    public function testTrimsWithCustomCharacters($input, $expectedResult)
    {
        $result = Str::trim($input, "NeverGonnaGiveYouUp");
        $this->assertSame($expectedResult, $result);
    }

    /** @provides testTrimsWithCustomCharacters */
    public function provideTrimsWithCustomCharacters()
    {
        return array(
            "It trims null" => array(null, ''),
            "It trims empty string" => array('', ''),
            "It doesn't trim spaces (1)" => array(' FOO', ' FOO'),
            "It doesn't trim spaces (2)" => array('bar ', 'bar '),
            "It trims front" => array('NeverGonnaGiveYouUp Never gonna let you down!', ' Never gonna let you down!'),
            "It doesn't touch the middle" => array('FOO N BAR', 'FOO N BAR')
        );
    }
}
