<?php

namespace SVG\Rasterization;

abstract class BaseRasterImage
{
    abstract public function toPng(string $path);
}
