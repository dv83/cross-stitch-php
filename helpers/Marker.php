<?php

namespace app\helpers;

/**
 * Class Marker
 *
 * @author Dmitry Mitko <dima@icecat.biz>
 */
class Marker
{
    protected const WHITE_FONT = [
        '000000',
        '0000FF',
    ];
    protected const COLORS = [
        'CCCC00',
        'FFFF66',
        'CC0000',
        'FFAAAA',
        'FF00FF',
        '7777FF',
        '0000FF',
        '99FFCC',
        '339966',
        'CCCCCC',
        '666666',
        'FFFFFF',
        '000000',
    ];
    protected const MARKERS = [
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
        'Б',
        'Г',
        'Д',
        'Ж',
        'И',
        'Л',
        'У',
        'Ф',
        'Ш',
        'Ы',
        'Э',
        'Ю',
        'Я',
        '\\',
        '/',
        '?',
        '№',
        '[',
        ']',
        '{',
        '}',
        '"',
        '|',
        '#',
        '=',
        '+',
        '%',
        '(',
        ')',
        ':',
        '@',
        '~',
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
            'fontColor' => in_array(static::COLORS[$this->colorIndex], static::WHITE_FONT) ? 'FFFFFF' : '000000',
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
