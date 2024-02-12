<?php

namespace SVG\Rasterization;

class GdRasterImage extends BaseRasterImage
{
    /**
     * @var \GdImage|resource|null
     */
    private $resource = null;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function toPng(string $path = null)
    {
        if ($path === null) {
            return imagepng($this->resource);
        }

        return imagepng($this->resource, $path);
    }

    public function getResource()
    {
        return $this->resource;
    }
}
