<?php

namespace Jangobrick\SVG\Nodes\Shapes;

use Jangobrick\SVG\Nodes\SVGNode;
use Jangobrick\SVG\SVG;
use Jangobrick\SVG\SVGRenderingHelper;

class SVGPath extends SVGNode
{
    private $d;

    public function __construct($d)
    {
        $this->d = $d;
        parent::__construct();
    }

    public function toXMLString()
    {
        $s  = '<path';

        $s .= ' d="'.$this->d.'"';

        if (!empty($this->styles)) {
            $s .= ' style="';
            foreach ($this->styles as $style => $value) {
                $s .= $style.': '.$value.'; ';
            }
            $s .= '"';
        }

        $s .= ' />';

        return $s;
    }

    public function draw(SVGRenderingHelper $rh, $scaleX, $scaleY, $offsetX = 0, $offsetY = 0)
    {
        $rh->push();

        $opacity = $this->getStyle('opacity');
        if (isset($opacity) && is_numeric($opacity)) {
            $opacity = floatval($opacity);
            $rh->scaleOpacity($opacity);
        }

        // original (document fragment) width for unit parsing
        $ow = $rh->getWidth() / $scaleX;

        // start of polygon construction

        $polys       = [];
        $currentPoly = null;

        $x      = 0;
        $y      = 0;
        $startX = null;
        $startY = null;

        $matches = [];
        preg_match_all('/[MLHVCQAZ][^MLHVCQAZ]*/i', $this->d, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $match   = trim($match[0]);
            $command = substr($match, 0, 1);

            $args = preg_split('/[\s,]+/', trim(substr($match, 1)));

            if ($command === 'M') {
                // moveto absolute

                foreach (array_chunk($args, 2) as $args) {
                    if (count($args) < 2) {
                        break 2;
                    }

                    $x      = floatval($args[0]);
                    $y      = floatval($args[1]);
                    $startX = $x;
                    $startY = $y;

                    if (!empty($currentPoly)) {
                        $polys[] = $currentPoly;
                    }
                    $currentPoly = [
                        ($offsetX + $x) * $scaleX,
                        ($offsetY + $y) * $scaleY,
                    ];
                }
            } elseif ($command === 'm') {
                // moveto relative

                foreach (array_chunk($args, 2) as $args) {
                    if (count($args) < 2) {
                        break 2;
                    }

                    $x += floatval($args[0]);
                    $y += floatval($args[1]);
                    $startX = $x;
                    $startY = $y;

                    if (!empty($currentPoly)) {
                        $polys[] = $currentPoly;
                    }
                    $currentPoly = [
                        ($offsetX + $x) * $scaleX,
                        ($offsetY + $y) * $scaleY,
                    ];
                }
            } elseif ($command === 'L') {
                // lineto absolute

                foreach (array_chunk($args, 2) as $args) {
                    if (count($args) < 2) {
                        break 2;
                    }

                    $x             = floatval($args[0]);
                    $y             = floatval($args[1]);
                    $currentPoly[] = ($offsetX + $x) * $scaleX;
                    $currentPoly[] = ($offsetY + $y) * $scaleY;
                }
            } elseif ($command === 'l') {
                // lineto relative

                foreach (array_chunk($args, 2) as $args) {
                    if (count($args) < 2) {
                        break 2;
                    }

                    $x += floatval($args[0]);
                    $y += floatval($args[1]);
                    $currentPoly[] = ($offsetX + $x) * $scaleX;
                    $currentPoly[] = ($offsetY + $y) * $scaleY;
                }
            } elseif ($command === 'H') {
                // lineto horizontal absolute

                if (empty($args)) {
                    break;
                }

                foreach ($args as $arg) {
                    $x             = floatval($arg);
                    $currentPoly[] = ($offsetX + $x) * $scaleX;
                    $currentPoly[] = ($offsetY + $y) * $scaleY;
                }
            } elseif ($command === 'h') {
                // lineto horizontal relative

                if (empty($args)) {
                    break;
                }

                foreach ($args as $arg) {
                    $x += floatval($arg);
                    $currentPoly[] = ($offsetX + $x) * $scaleX;
                    $currentPoly[] = ($offsetY + $y) * $scaleY;
                }
            } elseif ($command === 'V') {
                // lineto vertical absolute

                if (empty($args)) {
                    break;
                }

                foreach ($args as $arg) {
                    $y             = floatval($arg);
                    $currentPoly[] = ($offsetX + $x) * $scaleX;
                    $currentPoly[] = ($offsetY + $y) * $scaleY;
                }
            } elseif ($command === 'v') {
                // lineto vertical relative

                if (empty($args)) {
                    break;
                }

                foreach ($args as $arg) {
                    $y += floatval($arg);
                    $currentPoly[] = ($offsetX + $x) * $scaleX;
                    $currentPoly[] = ($offsetY + $y) * $scaleY;
                }
            } elseif ($command === 'Z' || $command === 'z') {
                // end

                if (!empty($args)) {
                    break;
                }

                $x             = $startX !== null ? $startX : 0;
                $y             = $startY !== null ? $startY : 0;
                $currentPoly[] = ($offsetX + $x) * $scaleX;
                $currentPoly[] = ($offsetY + $y) * $scaleY;
            } elseif ($command === 'C') {
                // curveto cubic absolute

                foreach (array_chunk($args, 6) as $args) {
                    if (count($args) < 6) {
                        break 2;
                    }

                    // start point
                    $p0x = ($offsetX + $x) * $scaleX;
                    $p0y = ($offsetY + $y) * $scaleY;
                    // first control point
                    $p1x = ($offsetX + floatval($args[0])) * $scaleX;
                    $p1y = ($offsetY + floatval($args[1])) * $scaleY;
                    // second control point
                    $p2x = ($offsetX + floatval($args[2])) * $scaleX;
                    $p2y = ($offsetY + floatval($args[3])) * $scaleY;
                    // final point
                    $nx  = floatval($args[4]);
                    $ny  = floatval($args[5]);
                    $p3x = ($offsetX + $nx) * $scaleX;
                    $p3y = ($offsetY + $ny) * $scaleY;

                    $currentPoly = array_merge($currentPoly,
                        SVGRenderingHelper::approximateCubicBezier(
                            [$p0x, $p0y],
                            [$p1x, $p1y],
                            [$p2x, $p2y],
                            [$p3x, $p3y]
                        )
                    );

                    $x = $nx;
                    $y = $ny;
                }
            } elseif ($command === 'c') {
                // curveto cubic relative

                foreach (array_chunk($args, 6) as $args) {
                    if (count($args) < 6) {
                        break 2;
                    }

                    // start point
                    $p0x = ($offsetX + $x) * $scaleX;
                    $p0y = ($offsetY + $y) * $scaleY;
                    // first control point
                    $p1x = ($offsetX + $x + floatval($args[0])) * $scaleX;
                    $p1y = ($offsetY + $y + floatval($args[1])) * $scaleY;
                    // second control point
                    $p2x = ($offsetX + $x + floatval($args[2])) * $scaleX;
                    $p2y = ($offsetY + $y + floatval($args[3])) * $scaleY;
                    // final point
                    $nx  = $x + floatval($args[4]);
                    $ny  = $y + floatval($args[5]);
                    $p3x = ($offsetX + $nx) * $scaleX;
                    $p3y = ($offsetY + $ny) * $scaleY;

                    $currentPoly = array_merge($currentPoly,
                        SVGRenderingHelper::approximateCubicBezier(
                            [$p0x, $p0y],
                            [$p1x, $p1y],
                            [$p2x, $p2y],
                            [$p3x, $p3y]
                        )
                    );

                    $x = $nx;
                    $y = $ny;
                }
            } elseif ($command === 'Q') {
                // curveto quadratic absolute

                foreach (array_chunk($args, 4) as $args) {
                    if (count($args) < 4) {
                        break 2;
                    }

                    // start point
                    $p0x = ($offsetX + $x) * $scaleX;
                    $p0y = ($offsetY + $y) * $scaleY;
                    // control point
                    $p1x = ($offsetX + floatval($args[0])) * $scaleX;
                    $p1y = ($offsetY + floatval($args[1])) * $scaleY;
                    // final point
                    $nx  = floatval($args[2]);
                    $ny  = floatval($args[3]);
                    $p2x = ($offsetX + $nx) * $scaleX;
                    $p2y = ($offsetY + $ny) * $scaleY;

                    $currentPoly = array_merge($currentPoly,
                        SVGRenderingHelper::approximateQuadraticBezier(
                            [$p0x, $p0y],
                            [$p1x, $p1y],
                            [$p2x, $p2y]
                        )
                    );

                    $x = $nx;
                    $y = $ny;
                }
            } elseif ($command === 'q') {
                // curveto quadratic relative

                foreach (array_chunk($args, 4) as $args) {
                    if (count($args) < 4) {
                        break 2;
                    }

                    // start point
                    $p0x = ($offsetX + $x) * $scaleX;
                    $p0y = ($offsetY + $y) * $scaleY;
                    // control point
                    $p1x = ($offsetX + $x + floatval($args[0])) * $scaleX;
                    $p1y = ($offsetY + $y + floatval($args[1])) * $scaleY;
                    // final point
                    $nx  = $x + floatval($args[2]);
                    $ny  = $y + floatval($args[3]);
                    $p2x = ($offsetX + $nx) * $scaleX;
                    $p2y = ($offsetY + $ny) * $scaleY;

                    $currentPoly = array_merge($currentPoly,
                        SVGRenderingHelper::approximateQuadraticBezier(
                            [$p0x, $p0y],
                            [$p1x, $p1y],
                            [$p2x, $p2y]
                        )
                    );

                    $x = $nx;
                    $y = $ny;
                }
            }
        }

        if (!empty($currentPoly)) {
            $polys[] = $currentPoly;
        }

        // fill
        $fill = $this->getComputedStyle('fill');
        if (isset($fill) && $fill !== 'none') {
            $fillColor = SVG::parseColor($fill, true);
            foreach ($polys as $poly) {
                $numpoints = count($poly) / 2;
                if ($numpoints >= 3) {
                    $rh->fillPolygon($poly, $numpoints, $fillColor);
                }
            }
        }

        // outline
        $stroke = $this->getComputedStyle('stroke');
        if (isset($stroke) && $stroke !== 'none') {
            $strokeColor = SVG::parseColor($stroke, true);
            $rh->setStrokeWidth(SVG::convertUnit($this->getComputedStyle('stroke-width'), $ow) * $scaleX);
            foreach ($polys as $poly) {
                $rh->drawPolyline($poly, count($poly) / 2, $strokeColor);
            }
        }

        $rh->pop();
    }
}
