<?php

namespace SVG\Fonts;

class FontRegistry
{
    private $fontFiles = [];

    public function addFont(string $filePath): void
    {
        $ttfFile = TrueTypeFontFile::read($filePath);
        if ($ttfFile === null) {
            throw new \RuntimeException('Font file "' . $filePath . '" is not a valid TrueType font.');
        }
        $this->fontFiles[] = $ttfFile;
    }

    public function findMatchingFont(?string $family, bool $italic, float $weight): ?FontFile
    {
        if (empty($this->fontFiles)) {
            return null;
        }

        // TODO implement generic families ('serif', 'sans-serif', 'monospace', etc.)

        // Check whether the requested font family is available, or whether we don't have to bother checking the family
        // in the following loops.
        $anyFontFamily = true;
        foreach ($this->fontFiles as $font) {
            if ($family === $font->getFamily()) {
                $anyFontFamily = false;
            }
        }

        // Attempt to find the closest-weight match with correct family and italicness.
        $match = $this->closestMatchBasedOnWeight(function (FontFile $font) use ($family, $anyFontFamily, $italic) {
            return ($anyFontFamily || $font->getFamily() === $family) && $font->isItalic() === $italic;
        }, $weight);

        // Attempt to match just based on the font family.
        $match = $match ?? $this->closestMatchBasedOnWeight(function (FontFile $font) use ($family, $anyFontFamily) {
            return $anyFontFamily || $font->getFamily() === $family;
        }, $weight);

        // Return any font at all, if possible.
        return $match ?? $this->fontFiles[0];
    }

    private function closestMatchBasedOnWeight(callable $filter, float $targetWeight): ?FontFile
    {
        $bestMatch = null;
        foreach ($this->fontFiles as $font) {
            if (!$filter($font)) {
                continue;
            }
            if ($bestMatch === null) {
                $bestMatch = $font;
                continue;
            }
            if (abs($targetWeight - $font->getWeight()) < abs($targetWeight - $bestMatch->getWeight())) {
                $bestMatch = $font;
            }
        }
        return $bestMatch;
    }
}
