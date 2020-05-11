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
    protected string $outputTextFile = '';

    /**
     * @var array
     */
    protected array $report = [];

    /**
     * Execute image
     */
    public function execute()
    {
        // output file
        $inputImage = imagecreatefromjpeg($this->inputFile);

        $width = imagesx($inputImage);
        $height = imagesy($inputImage);

        $outputImage = imagecreatetruecolor($width, $height);

        $map = new Map();

        for ($x = 0; $x < $width; $x++) {
            print $x . ' or ' . $width . "\n";

            for ($y = 0; $y < $height; $y++) {
                // get pixel
                [$r, $g, $b] = $this->indextoRgb(imagecolorat($inputImage, $x, $y));

                // get mapped rgb
                $mappedData = $map->getDMC($r, $g, $b);
                $i = 0;
                //if (count($mappedData) === 1) {
                    $rgb = $mappedData[$i]['rgb'];
                //} else {
                //    $i = ($x + $y) % 2 === 0 ? 1 : 2;
                //    $rgb = $mappedData[$i]['rgb'];
                //}
                [$nr, $ng, $nb] = explode('x', $rgb);

                // report
                if (!isset($this->report[$mappedData[$i]['code']])) {
                    $this->report[$mappedData[$i]['code']] = 1;
                } else {
                    $this->report[$mappedData[$i]['code']]++;
                }

                // set pixel
                $color = imagecolorallocate($outputImage, $nr, $ng, $nb);
                imagesetpixel($outputImage, $x, $y, $color);
            }
        }

        imagebmp($outputImage, $this->outputFile);

        // report
        $textFile = fopen($this->outputTextFile, 'w');

        asort($this->report);

        foreach (array_reverse($this->report) as $code => $number) {
            $code = str_repeat(' ', (30 - strlen($code))) . $code;
            fwrite($textFile, $code . ': ' . $number . "\r\n");
        }

        fclose($textFile);
    }

    /**
     * Index to RGB
     *
     * @param int $rgb
     *
     * @return int[]
     */
    protected function indextoRgb(int $rgb)
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
    }

    /**
     * Set output file
     *
     * @param string $outputFile
     */
    public function setOutputFile(string $outputFile): void
    {
        $this->outputFile = $outputFile;
    }

    /**
     * @param string $outputTextFile
     */
    public function setOutputTextFile(string $outputTextFile): void
    {
        $this->outputTextFile = $outputTextFile;
    }
}
