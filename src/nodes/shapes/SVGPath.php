<?php

class SVGPath extends SVGNode {

    private $d;



    public function __construct($d) {
        $this->d = $d;
    }





    public function toXMLString() {

        $s  = '<path';

        $s .= ' d="'.$this->d.'"';

        if (!empty($this->styles)) {
            $s .= ' style="';
            foreach ($this->styles as $style => $value) {
                $s .= $style . ': ' . $value . '; ';
            }
            $s .= '"';
        }

        $s .= ' />';

        return $s;

    }





    public function draw(SVGRenderingHelper $rh, $scaleX, $scaleY, $offsetX = 0, $offsetY = 0) {

        $rh->push();

        $opacity = $this->getStyle('opacity');
        if (isset($opacity) && is_numeric($opacity)) {
            $opacity = floatval($opacity);
            $rh->scaleOpacity($opacity);
        }

        // original (document fragment) width for unit parsing
        $ow = $rh->getWidth() / $scaleX;



        // start of polygon construction

        $polys = array();
        $currentPoly = null;

        $x = 0;
        $y = 0;
        $startX = null;
        $startY = null;

        $matches = array();
        preg_match_all('/[MLCQAZ][^MLCQAZ]*/i', $this->d, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {

            $match = trim($match[0]);
            $command = substr($match, 0, 1);

            $args = preg_split('/[\s,]+/', trim(substr($match, 1)));

            if ($command === 'M') {
                // moveto absolute
                $x = floatval($args[0]);
                $y = floatval($args[1]);
                $startX = $x;
                $startY = $y;
                if (!empty($currentPoly)) {
                    $polys[] = $currentPoly;
                }
                $currentPoly = array();
            } else if ($command === 'm') {
                // moveto relative
                $x += floatval($args[0]);
                $y += floatval($args[1]);
                $startX = $x;
                $startY = $y;
                if (!empty($currentPoly)) {
                    $polys[] = $currentPoly;
                }
                $currentPoly = array();
            } else if ($command === 'L') {
                // lineto absolute
                $x = floatval($args[0]);
                $y = floatval($args[1]);
                $currentPoly[] = ($offsetX + $x) * $scaleX;
                $currentPoly[] = ($offsetY + $y) * $scaleY;
            } else if ($command === 'l') {
                // lineto relative
                $x += floatval($args[0]);
                $y += floatval($args[1]);
                $currentPoly[] = ($offsetX + $x) * $scaleX;
                $currentPoly[] = ($offsetY + $y) * $scaleY;
            } else if ($command === 'Z' || $command === 'z') {
                // end
                $x = $startX !== null ? $startX : 0;
                $y = $startY !== null ? $startY : 0;
                $currentPoly[] = ($offsetX + $x) * $scaleX;
                $currentPoly[] = ($offsetY + $y) * $scaleY;
            } else if ($command === 'C') {
                $p0 = array(($offsetX + $x) * $scaleX, ($offsetY + $y) * $scaleY);
                $p1 = array(($offsetX + floatval($args[0])) * $scaleX, ($offsetY + floatval($args[1])) * $scaleY);
                $p2 = array(($offsetX + floatval($args[2])) * $scaleX, ($offsetY + floatval($args[3])) * $scaleY);
                $nx = floatval($args[4]);
                $ny = floatval($args[5]);
                $p3 = array(($offsetX + $nx) * $scaleX, ($offsetY + $ny) * $scaleY);
                $currentPoly = array_merge($currentPoly, SVGRenderingHelper::approximateCubicBezier($p0, $p1, $p2, $p3));
                $x = $nx;
                $y = $ny;
            } else if ($command === 'c') {
                $p0 = array(($offsetX + $x) * $scaleX, ($offsetY + $y) * $scaleY);
                $p1 = array(($offsetX + $x + floatval($args[0])) * $scaleX, ($offsetY + $y + floatval($args[1])) * $scaleY);
                $p2 = array(($offsetX + $x + floatval($args[2])) * $scaleX, ($offsetY + $y + floatval($args[3])) * $scaleY);
                $nx = $x + floatval($args[4]);
                $ny = $y + floatval($args[5]);
                $p3 = array(($offsetX + $nx) * $scaleX, ($offsetY + $ny) * $scaleY);
                $currentPoly = array_merge($currentPoly, SVGRenderingHelper::approximateCubicBezier($p0, $p1, $p2, $p3));
                $x = $nx;
                $y = $ny;
            } else if ($command === 'Q') {
                $p0 = array(($offsetX + $x) * $scaleX, ($offsetY + $y) * $scaleY);
                $p1 = array(($offsetX + floatval($args[0])) * $scaleX, ($offsetY + floatval($args[1])) * $scaleY);
                $nx = floatval($args[2]);
                $ny = floatval($args[3]);
                $p2 = array(($offsetX + $nx) * $scaleX, ($offsetY + $ny) * $scaleY);
                $currentPoly = array_merge($currentPoly, SVGRenderingHelper::approximateQuadraticBezier($p0, $p1, $p2));
                $x = $nx;
                $y = $ny;
            } else if ($command === 'q') {
                $p0 = array(($offsetX + $x) * $scaleX, ($offsetY + $y) * $scaleY);
                $p1 = array(($offsetX + $x + floatval($args[0])) * $scaleX, ($offsetY + $y + floatval($args[1])) * $scaleY);
                $nx = $x + floatval($args[2]);
                $ny = $y + floatval($args[3]);
                $p2 = array(($offsetX + $nx) * $scaleX, ($offsetY + $ny) * $scaleY);
                $currentPoly = array_merge($currentPoly, SVGRenderingHelper::approximateQuadraticBezier($p0, $p1, $p2));
                $x = $nx;
                $y = $ny;
            }

        }

        if (!empty($currentPoly)) {
            $polys[] = $currentPoly;
        }



        // outline

        $fill = $this->getComputedStyle('fill');
        if (isset($fill) && $fill !== 'none') {
            $fillColor = SVG::parseColor($fill, true);
            foreach ($polys as $poly) {
                $rh->fillPolygon($poly, count($poly) / 2, $fillColor);
            }
        }

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
