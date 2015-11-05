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

        $stroke = $this->getComputedStyle('stroke');
        if (isset($stroke) && $stroke !== 'none') {

            $strokeColor = SVG::parseColor($stroke, true);
            $rh->setStrokeWidth(SVG::convertUnit($this->getComputedStyle('stroke-width'), $ow) * $scaleX);

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
                } else if ($command === 'm') {
                    // moveto relative
                    $x += floatval($args[0]);
                    $y += floatval($args[1]);
                } else if ($command === 'L') {
                    // lineto absolute
                    $nx = floatval($args[0]);
                    $ny = floatval($args[1]);
                    $rh->drawLine(
                        ($offsetX + $x) * $scaleX, ($offsetY + $y) * $scaleY, // x1, y1
                        ($offsetX + $nx) * $scaleX, ($offsetY + $ny) * $scaleY, // x2, y2
                        $strokeColor
                    );
                    $x = $nx;
                    $y = $ny;
                } else if ($command === 'l') {
                    // lineto relative
                    $nx = $x + floatval($args[0]);
                    $ny = $y + floatval($args[1]);
                    $rh->drawLine(
                        ($offsetX + $x) * $scaleX, ($offsetY + $y) * $scaleY, // x1, y1
                        ($offsetX + $nx) * $scaleX, ($offsetY + $ny) * $scaleY, // x2, y2
                        $strokeColor
                    );
                    $x = $nx;
                    $y = $ny;
                } else if ($command === 'Z' || $command === 'z') {
                    // end
                    $nx = $startX !== null ? $startX : 0;
                    $ny = $startY !== null ? $startY : 0;
                    $rh->drawLine(
                        ($offsetX + $x) * $scaleX, ($offsetY + $y) * $scaleY, // x1, y1
                        ($offsetX + $nx) * $scaleX, ($offsetY + $ny) * $scaleY, // x2, y2
                        $strokeColor
                    );
                    $x = $nx;
                    $y = $ny;
                } else if ($command === 'C') {
                    $p0 = array(($offsetX + $x) * $scaleX, ($offsetY + $y) * $scaleY);
                    $p1 = array(($offsetX + floatval($args[0])) * $scaleX, ($offsetY + floatval($args[1])) * $scaleY);
                    $p2 = array(($offsetX + floatval($args[2])) * $scaleX, ($offsetY + floatval($args[3])) * $scaleY);
                    $nx = floatval($args[4]);
                    $ny = floatval($args[5]);
                    $p3 = array(($offsetX + $nx) * $scaleX, ($offsetY + $ny) * $scaleY);
                    $rh->drawCubicBezier($p0, $p1, $p2, $p3, $strokeColor);
                    $x = $nx;
                    $y = $ny;
                } else if ($command === 'c') {
                    $p0 = array(($offsetX + $x) * $scaleX, ($offsetY + $y) * $scaleY);
                    $p1 = array(($offsetX + $x + floatval($args[0])) * $scaleX, ($offsetY + $y + floatval($args[1])) * $scaleY);
                    $p2 = array(($offsetX + $x + floatval($args[2])) * $scaleX, ($offsetY + $y + floatval($args[3])) * $scaleY);
                    $nx = $x + floatval($args[4]);
                    $ny = $y + floatval($args[5]);
                    $p3 = array(($offsetX + $nx) * $scaleX, ($offsetY + $ny) * $scaleY);
                    $rh->drawCubicBezier($p0, $p1, $p2, $p3, $strokeColor);
                    $x = $nx;
                    $y = $ny;
                } else if ($command === 'Q') {
                    $p0 = array(($offsetX + $x) * $scaleX, ($offsetY + $y) * $scaleY);
                    $p1 = array(($offsetX + floatval($args[0])) * $scaleX, ($offsetY + floatval($args[1])) * $scaleY);
                    $nx = floatval($args[2]);
                    $ny = floatval($args[3]);
                    $p2 = array(($offsetX + $nx) * $scaleX, ($offsetY + $ny) * $scaleY);
                    $rh->drawQuadraticBezier($p0, $p1, $p2, $strokeColor);
                    $x = $nx;
                    $y = $ny;
                } else if ($command === 'q') {
                    $p0 = array(($offsetX + $x) * $scaleX, ($offsetY + $y) * $scaleY);
                    $p1 = array(($offsetX + $x + floatval($args[0])) * $scaleX, ($offsetY + $y + floatval($args[1])) * $scaleY);
                    $nx = $x + floatval($args[2]);
                    $ny = $y + floatval($args[3]);
                    $p2 = array(($offsetX + $nx) * $scaleX, ($offsetY + $ny) * $scaleY);
                    $rh->drawQuadraticBezier($p0, $p1, $p2, $strokeColor);
                    $x = $nx;
                    $y = $ny;
                }

            }

        }

        $rh->pop();

    }

}
