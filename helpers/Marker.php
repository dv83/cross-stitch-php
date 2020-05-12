<?php

namespace app\helpers;

/**
 * Class Marker
 *
 * @author Dmitry Mitko <dima@icecat.biz>
 */
class Marker
{
    protected const COLORS = [
        'CCCC00',
        '996600',
        'CC6600',
        'FF6666',
        '996666',
        'CC3399',
        '9900FF',
        'BBBBBB',
        '6699CC',
        '33CC66',
        'CCFF00',
        '777777',
        'FFFFFF',
    ];
    protected const MARKERS = [
        '#',
        '=',
        '+',
        '%',
        '(',
        ')',
        ':',
        '@',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        '<',
        '>',
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'R',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
    ];

    /**
     * @var int
     */
    protected int $colorIndex = 0;

    /**
     * @var int
     */
    protected int $markerIndex = 0;

    /**
     * Get the next symbol
     */
    public function next(): array
    {
        $output = [
            'value' => static::MARKERS[$this->markerIndex],
            'color' => static::COLORS[$this->colorIndex],
        ];

        $this->markerIndex++;
        if ($this->markerIndex === count(static::MARKERS)) {
            $this->markerIndex = 0;
        }
        $this->colorIndex++;
        if ($this->colorIndex === count(static::COLORS)) {
            $this->colorIndex = 0;
        }

        return $output;
    }
}
