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
        return [
            "It trims null" => [null, ""],
            "It trims empty string" => ["", ""],
            "It trims front" => [" foo", "foo"],
            "It trims end" => ["bar ", "bar"],
            "It trims front and end" => [" foobar ", 'foobar'],
            "It doesn't touch the middle" => [" foo baz ", 'foo baz'],
            "It trims tabs" => [" \t foo", 'foo'],
            "It trims carriage returns" => ["bar \r", 'bar'],
            "It trims NUL-byte" => ["foobar \0", 'foobar'],
            "It trims vertical tab" => ["foobaz \v", 'foobaz'],
        ];
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
        return [
            "It trims null" => [null, ''],
            "It trims empty string" => ['', ''],
            "It doesn't trim spaces (1)" => [' FOO', ' FOO'],
            "It doesn't trim spaces (2)" => ['bar ', 'bar '],
            "It trims front" => ['NeverGonnaGiveYouUp Never gonna let you down!', ' Never gonna let you down!'],
            "It doesn't touch the middle" => ['FOO N BAR', 'FOO N BAR']
        ];
    }
}
