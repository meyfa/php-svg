<?php

namespace JangoBrick\SVG\Rasterization\Path;

class SVGPathParser
{
    private static $commandLengths = array(
        'M' => 2,   'm' => 2,   // MoveTo
        'L' => 2,   'l' => 2,   // LineTo
        'H' => 1,   'h' => 1,   // LineToHorizontal
        'V' => 1,   'v' => 1,   // LineToVertical
        'C' => 6,   'c' => 6,   // CurveToCubic
        'Q' => 4,   'q' => 4,   // CurveToQuadratic
        'A' => 7,   'a' => 7,   // ArcTo
        'Z' => 0,   'z' => 0,   // ClosePath
    );

    public function parse($description)
    {
        $commands = array();

        $matches = array();
        preg_match_all('/[MLHVCQAZ][^MLHVCQAZ]*/i', $description, $matches);

        foreach ($matches[0] as $match) {
            $match = trim($match);

            $id   = substr($match, 0, 1);
            $args = trim(substr($match, 1));
            $args = empty($args) ? array() : preg_split('/[\s,]+/', $args);

            $success = $this->parseCommandChain($id, $args, $commands);
            if (!$success) {
                break;
            }
        }

        return $commands;
    }

    private function parseCommandChain($id, array $args, array &$commands)
    {
        if (!isset(self::$commandLengths[$id])) {
            // unknown command
            return false;
        }

        $length = self::$commandLengths[$id];

        if ($length === 0) {
            if (count($args) > 0) {
                return false;
            }
            $commands[] = array(
                'id'    => $id,
                'args'  => $args,
            );
            return true;
        }

        foreach (array_chunk($args, $length) as $subArgs) {
            if (count($subArgs) !== $length) {
                return false;
            }
            $commands[] = array(
                'id'    => $id,
                'args'  => array_map('floatval', $subArgs),
            );
        }

        return true;
    }
}
