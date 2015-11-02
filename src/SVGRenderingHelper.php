<?php

class SVGRenderingHelper {

    private $image, $imageWidth, $imageHeight;
    private $strokeWidth;
    private $state, $stateStack;



    public function __construct($image, $imageWidth, $imageHeight) {

        $this->image = $image;
        $this->imageWidth = $imageWidth;
        $this->imageHeight = $imageHeight;

        $this->state = array(
            'x' => 0,
            'y' => 0,
            'opacity' => 1
        );
        $this->stateStack = array(&$this->state);

    }





    public function getWidth() {
        return $this->imageWidth;
    }

    public function getHeight() {
        return $this->imageHeight;
    }





    public function setStrokeWidth($strokeWidth) {
        $this->strokeWidth = $strokeWidth;
        imagesetthickness($this->image, $strokeWidth);
    }





    public function push() {
        // create copy
        $this->state = $this->state;
        $this->stateStack[] = $this->state;
    }

    public function pop() {
        if (count($this->stateStack) === 1)
            throw new RuntimeException("No more states to pop");
        $this->state = array_pop($this->stateStack);
    }





    public function translate($x, $y) {
        $this->state['x'] += $x;
        $this->state['y'] += $y;
    }





    public function scaleOpacity($value) {
        $this->state['opacity'] *= $value;
        if ($this->state['opacity'] < 0)
            $this->state['opacity'] = 0;
        elseif ($this->state['opacity'] > 1)
            $this->state['opacity'] = 1;
    }

    // multiplies the alpha channel of the given color with the given factor
    // example: color has 0.5 opacity, factor is 0.2 => result is 0.1
    // the color is a "normal" GD color integer, e.g. 0x7F000000 for fully transparent black
    private static function _multiplyColorAlpha($color, $alphaFactor) {
        $rgb = $color & 0x00FFFFFF;
        $a = ($color & 0xFF000000) >> 24;
        $a = 0x7F - 0x7F*$alphaFactor + $a*$alphaFactor;
        return $rgb | ($a << 24);
    }





    public function createBuffer() {

        $buffer = imagecreatetruecolor($this->imageWidth, $this->imageHeight);
        imagealphablending($buffer, true);
        imagesavealpha($buffer, true);
        imagefill($buffer, 0, 0, 0x7F000000);

        return new SVGRenderingHelper($buffer, $this->imageWidth, $this->imageHeight);

    }

    public function drawBuffer(SVGRenderingHelper $buffer, $opacity = 1.0) {

        if ($opacity > 1)
            $opacity = 1;
        elseif ($opacity < 0)
            $opacity = 0;

        // imagecopymerge ignores alpha channel, so we have to change the alpha
        // manually and then do imagecopy (which DOES preserve alpha)

        imagealphablending($buffer->image, false);

        if ($opacity < 1) {
            for ($x=0; $x<$this->imageWidth; $x++) {
                for ($y=0; $y<$this->imageHeight; $y++) {
                    $color = imagecolorat($buffer->image, $x, $y);
                    $color = self::_multiplyColorAlpha($color, $opacity);
                    imagesetpixel($buffer->image, $x, $y, $color);
                }
            }
        }

        imagecopy(
            $this->image, $buffer->image, // dst, src
            0, 0, // dst x, dst y
            0, 0, // src x, src y
            $this->imageWidth, $this->imageHeight // src w, src h
        );

        // the process above renders the buffer useless (hah, puns), so we can
        // destroy it without making the situation much worse
        imagedestroy($buffer->image);

    }





    public function drawRect($x, $y, $width, $height, $color) {

        $x += $this->state['x'];
        $y += $this->state['y'];
        $color = self::_multiplyColorAlpha($color, $this->state['opacity']);

        // imagerectangle draws left and right side 1px thicker than it should,
        // so we draw four lines instead
        // (it works, don't screw with it)

        if ($this->strokeWidth > 1) {
            $t = floor($this->strokeWidth / 2);
        } else {
            $t = 0;
        }

        // order: top, bottom, left, right
        imageline($this->image, $x - $t,     $y,           $x + $width + $t - 1, $y,                    $color);
        imageline($this->image, $x - $t,     $y + $height, $x + $width + $t - 1, $y + $height,          $color);
        imageline($this->image, $x,          $y + $t,      $x,                   $y + $height - $t - 1, $color);
        imageline($this->image, $x + $width, $y + $t,      $x + $width,          $y + $height - $t - 1, $color);

    }

    public function fillRect($x, $y, $width, $height, $color) {
        $x += $this->state['x'];
        $y += $this->state['y'];
        $color = self::_multiplyColorAlpha($color, $this->state['opacity']);
        imagefilledrectangle($this->image, $x, $y, $x + $width - 1, $y + $height - 1, $color);
    }





    public function drawEllipse($cx, $cy, $rx, $ry, $color) {
        $cx += $this->state['x'];
        $cy += $this->state['y'];
        $color = self::_multiplyColorAlpha($color, $this->state['opacity']);
        // imageellipse ignores imagesetthickness; draw arc instead
        imagearc($this->image, $cx, $cy, $rx * 2, $ry * 2, 0, 360, $color);
    }

    public function fillEllipse($cx, $cy, $rx, $ry, $color) {
        $cx += $this->state['x'];
        $cy += $this->state['y'];
        $color = self::_multiplyColorAlpha($color, $this->state['opacity']);
        imagefilledellipse($this->image, $cx, $cy, $rx * 2, $ry * 2, $color);
    }





    public function drawLine($x1, $y1, $x2, $y2, $color) {
        $x1 += $this->state['x'];
        $y1 += $this->state['y'];
        $x2 += $this->state['x'];
        $y2 += $this->state['y'];
        $color = self::_multiplyColorAlpha($color, $this->state['opacity']);
        imageline($this->image, $x1, $y1, $x2, $y2, $color);
    }





    public function drawPolygon($points, $numpoints, $color) {
        for ($i=0, $n=count($points); $i<$n; $i++) {
            $points[$i] += $this->state[$i % 2 === 0 ? 'x' : 'y'];
        }
        $color = self::_multiplyColorAlpha($color, $this->state['opacity']);
        imagepolygon($this->image, $points, $numpoints, $color);
    }

    public function fillPolygon($points, $numpoints, $color) {
        for ($i=0, $n=count($points); $i<$n; $i++) {
            $points[$i] += $this->state[$i % 2 === 0 ? 'x' : 'y'];
        }
        $color = self::_multiplyColorAlpha($color, $this->state['opacity']);
        imagefilledpolygon($this->image, $points, $numpoints, $color);
    }



    public function drawPolyline($points, $numpoints, $color) {

        $color = self::_multiplyColorAlpha($color, $this->state['opacity']);

        for ($i=1; $i<$numpoints; $i++) {
            $x1 = $this->state['x'] + $points[($i-1) * 2];
            $y1 = $this->state['y'] + $points[($i-1) * 2 + 1];
            $x2 = $this->state['x'] + $points[$i * 2];
            $y2 = $this->state['y'] + $points[$i * 2 + 1];
            imageline($this->image, $x1, $y1, $x2, $y2, $color);
        }

    }





    // $p0 start, $p1 first control point, $p2 second control point, $p3 end
    public function drawBezierCurve($p0, $p1, $p2, $p3, $color) {
        $poly = self::approximateBezierCurve($p0, $p1, $p2, $p3);
        $this->drawPolyline($poly, count($poly) / 2, $color);
    }

    public static function approximateBezierCurve($p0, $p1, $p2, $p3, $accuracy = 1) {

        $tPrev = 0.0;
        $prev = $p0;
        $poly = array($p0[0], $p0[1]);

        while ($tPrev < 1) {
            if ($tPrev + $step > 1.0) {
                $step = 1 - $tPrev;
            }
            $step = 0.1;
            $point = self::_bezier($p0, $p1, $p2, $p3, $tPrev + $step);
            $dist = self::_pointSqDist($prev, $point);
            while ($dist > $accuracy) {
                $step /= 2;
                $point = self::_bezier($p0, $p1, $p2, $p3, $tPrev + $step);
                $dist = self::_pointSqDist($prev, $point);
            }
            $poly[] = $point[0];
            $poly[] = $point[1];
            $tPrev += $step;
            $prev = $point;
        }

        return $poly;

    }

    private static function _bezier($p0, $p1, $p2, $p3, $t) {

        $ti = 1 - $t;

        // first step: lines between the given points
        $a0x = $ti*$p0[0] + $t*$p1[0];
        $a0y = $ti*$p0[1] + $t*$p1[1];
        $a1x = $ti*$p1[0] + $t*$p2[0];
        $a1y = $ti*$p1[1] + $t*$p2[1];
        $a2x = $ti*$p2[0] + $t*$p3[0];
        $a2y = $ti*$p2[1] + $t*$p3[1];

        // second step: lines between points from step 2
        $b0x = $ti*$a0x + $t*$a1x;
        $b0y = $ti*$a0y + $t*$a1y;
        $b1x = $ti*$a1x + $t*$a2x;
        $b1y = $ti*$a1y + $t*$a2y;

        // last step: line between points from step 3, result
        return array($ti*$b0x + $t*$b1x, $ti*$b0y + $t*$b1y);

    }

    private static function _pointSqDist($p1, $p2) {
        $dx = $p2[0] - $p1[0];
        $dy = $p2[1] - $p1[1];
        return $dx * $dx + $dy * $dy;
    }

}
