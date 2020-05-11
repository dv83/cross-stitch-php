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
     * Execute image
     */
    public function execute(): void
    {
        // output file
        $inputImage = imagecreatefromjpeg($this->inputFile);

        $width = imagesx($inputImage);
        $height = imagesy($inputImage);

        $outputImage = imagecreatetruecolor($width, $height);

        $map = new Map();
        if ($this->mixedMode) {
            $map->setMixed(true);
        }

        for ($x = 0; $x < $width; $x++) {
            print $x . ' or ' . $width . "\n";

            for ($y = 0; $y < $height; $y++) {
                // get pixel
                [$r, $g, $b] = $this->indexToRgb(imagecolorat($inputImage, $x, $y));

                // get mapped rgb
                $mappedData = $map->getDMCColor($r, $g, $b);
                $i = 0;
                if ($this->chessMode && count($mappedData) !== 1) {
                    $i = ($x + $y) % 2 === 0 ? 1 : 2;
                }
                [$nr, $ng, $nb] = explode('x', $mappedData[$i]['rgb']);

                // report
                if (!isset($this->report[$mappedData[$i]['code']])) {
                    $this->report[$mappedData[$i]['code']] = 1;
                } else {
                    $this->report[$mappedData[$i]['code']]++;
                }

                // simple report
                if (count($mappedData) > 1) { // mixed cross
                    if (!isset($this->simpleReport[$mappedData[1]['code']])) {
                        $this->simpleReport[$mappedData[1]['code']] = 0.5;
                    } else {
                        $this->simpleReport[$mappedData[1]['code']] += 0.5;
                    }

                    if (!isset($this->simpleReport[$mappedData[2]['code']])) {
                        $this->simpleReport[$mappedData[2]['code']] = 0.5;
                    } else {
                        $this->simpleReport[$mappedData[2]['code']] += 0.5;
                    }
                } else { // simple cross
                    if (!isset($this->simpleReport[$mappedData[0]['code']])) {
                        $this->simpleReport[$mappedData[0]['code']] = 1;
                    } else {
                        $this->simpleReport[$mappedData[0]['code']]++;
                    }
                }

                // set pixel
                $color = imagecolorallocate($outputImage, $nr, $ng, $nb);
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
            fwrite($textFile, $code . ': ' . $number . "\r\n");
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
