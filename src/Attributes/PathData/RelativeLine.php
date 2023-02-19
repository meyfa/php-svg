<?php

namespace SVG\Attributes\PathData;

class RelativeLine extends RelativeMove
{
    public static function getNames(): array
    {
        return ['l'];
    }

    public function getName(): string
    {
        return 'l';
    }
}
