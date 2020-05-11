<?php

namespace app\helpers;

/**
 * Class Image
 *
 * @author Dmitry Mitko <dima@icecat.biz>
 */
class Image
{
    /**
     * @var string
     */
    protected string $inputFile = '';

    /**
     * @var string
     */
    protected string $outputFile = '';

    /**
     * @var string
     */
    protected string $outputReportFile = '';

    /**
     * @var string
     */
    protected string $outputReportTwoFile = '';

    /**
     * @var bool
     */
    protected bool $chessMode = false;

    /**
     * @var bool
     */
    protected bool $mixedMode = true;

    /**
     * @var array [ code => number of crosses ]
     */
    protected array $report = [];

    /**
     * @var array [ simple code => number of crosses ]
     */
    protected array $simpleReport = [];

    /**
     * @var array [ rgb => [x1, y1], ... ]
     */
    protected array $rgbList = [];

    /**
     * Execute image
     */
    public function execute(): void
    {
        // output file
        $inputImage = imagecreatefromjpeg($this->inputFile);

        $width = imagesx($inputImage);
        $height = imagesy($inputImage);

        $minCrosses = round($width * $height / 1000);

        $map = new Map();
        if ($this->mixedMode) {
            $map->setMixed(true);
        }

        print "\tread image\n";

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                // get pixel
                [$r, $g, $b] = $this->indexToRgb(imagecolorat($inputImage, $x, $y));

                // get mapped rgb
                $mappedData = $map->getDMCColor($r, $g, $b);
                $i = 0;
                if ($this->chessMode && count($mappedData) !== 1) {
                    $i = ($x + $y) % 2 === 0 ? 1 : 2;
                }

                // rgb list
                $this->rgbList[$mappedData[$i]['rgb']][] = [$x, $y];
            }
        }

        print "\tcollect >=" . $minCrosses . " rgbs\n";

        $newRgbList = [];

        // reorganize rare rgb
        $rgbList100 = [];
        foreach ($this->rgbList as $rgb => $list) {
            if (count($list) >= $minCrosses) {
                foreach ($list as $pair) {
                    $newRgbList[$rgb][] = $pair;
                }

                $rgbList100[] = $rgb;
            }
        }
        $map->reduceMap($rgbList100);

        print "\treplace <" . $minCrosses . " with >=" . $minCrosses . " rgbs\n";

        // replace rare rgb with often rgb
        foreach ($this->rgbList as $rgb => $list) {
            if (count($list) >= $minCrosses) {
                continue;
            }

            [$r, $g, $b] = explode('x', $rgb);
            $mappedData = $map->getDMCColor($r, $g, $b);
            $newRgb = $mappedData[0]['rgb'];

            // join 2 arrays and remove old array
            foreach ($list as $pair) {
                $newRgbList[$newRgb][] = $pair;
            }
        }

        print "\twrite image\n";

        // output file
        $outputImage = imagecreatetruecolor($width, $height);

        // prepare report
        foreach ($newRgbList as $rgb => $list) {
            if (count($list) === 0) {
                continue;
            }

            $mappedData = $map->getColor($rgb);

            // report
            if (isset($this->report[$mappedData[0]['code']])) {
                $this->report[$mappedData[0]['code']] += count($list);
            } else {
                $this->report[$mappedData[0]['code']] = count($list);
            }

            // simple report
            if (count($mappedData) > 1) { // mixed cross
                if (isset($this->simpleReport[$mappedData[1]['code']])) {
                    $this->simpleReport[$mappedData[1]['code']] += round(count($list) / 2);
                } else {
                    $this->simpleReport[$mappedData[1]['code']] = round(count($list) / 2);
                }

                if (isset($this->simpleReport[$mappedData[2]['code']])) {
                    $this->simpleReport[$mappedData[2]['code']] += round(count($list) / 2);
                } else {
                    $this->simpleReport[$mappedData[2]['code']] = round(count($list) / 2);
                }
            } else { // simple cross
                if (isset($this->simpleReport[$mappedData[0]['code']])) {
                    $this->simpleReport[$mappedData[0]['code']] += count($list);
                } else {
                    $this->simpleReport[$mappedData[0]['code']] = count($list);
                }
            }

            // set pixel
            [$r, $g, $b] = explode('x', $rgb);
            $color = imagecolorallocate($outputImage, $r, $g, $b);
            foreach ($list as [$x, $y]) {
                imagesetpixel($outputImage, $x, $y, $color);
            }
        }

        // write image
        imagebmp($outputImage, $this->outputFile);

        // preparing report
        $textFile = fopen($this->outputReportFile, 'w');
        asort($this->report);
        foreach (array_reverse($this->report) as $code => $number) {
            $code = str_repeat(' ', (30 - strlen($code))) . $code;
            fwrite($textFile, $code . ': ' . round($number) . "\r\n");
        }
        fclose($textFile);

        // preparing report
        $textFileTwo = fopen($this->outputReportTwoFile, 'w');
        asort($this->simpleReport);
        foreach (array_reverse($this->simpleReport) as $code => $number) {
            $code = str_repeat(' ', (30 - strlen($code))) . $code;
            fwrite($textFileTwo, $code . ': ' . round($number) . "\r\n");
        }
        fclose($textFileTwo);
    }

    /**
     * Index to RGB
     *
     * @param int $rgb
     *
     * @return int[]
     */
    protected function indexToRgb(int $rgb): array
    {
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        return [$r, $g, $b];
    }

    /**
     * Set input file
     *
     * @param string $inputFile
     */
    public function setInputFile(string $inputFile): void
    {
        $this->inputFile = $inputFile;
        $this->outputFile = $inputFile . '.new.bmp';
        $this->outputReportFile = $inputFile . '.report.txt';
        $this->outputReportTwoFile = $inputFile . '.reportTwo.txt';
    }

    /**
     * @param bool $chessMode
     */
    public function setChessMode(bool $chessMode): void
    {
        $this->chessMode = $chessMode;
    }

    /**
     * @param bool $mixedMode
     */
    public function setMixedMode(bool $mixedMode): void
    {
        $this->mixedMode = $mixedMode;
    }
}
