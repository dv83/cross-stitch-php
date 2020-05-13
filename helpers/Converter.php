<?php

namespace app\helpers;

/**
 * Class Converter
 *
 * @author Dmitry Mitko <dima@icecat.biz>
 */
class Converter
{
    /**
     * Convert N10xN10xN10 to N16N16N16
     *
     * @param string $rgb
     *
     * @return string
     */
    public static function rgbToHexRgb(string $rgb): string
    {
        [$r, $g, $b] = explode('x', $rgb);

        $r2 = strtoupper(dechex($r));
        $r2 = strlen($r2) === 1 ? '0' . $r2 : $r2;
        $g2 = strtoupper(dechex($g));
        $g2 = strlen($g2) === 1 ? '0' . $g2 : $g2;
        $b2 = strtoupper(dechex($b));
        $b2 = strlen($b2) === 1 ? '0' . $b2 : $b2;

        return $r2 . $g2 . $b2;
    }
}
