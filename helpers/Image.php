<?php

namespace app\helpers;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Class Image
 *
 * @author Dmitry Mitko <dima@icecat.biz>
 */
class Image
{
    protected const CELL_SIZE = 16;

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
     * @var string
     */
    protected string $outputXlsxFile = '';

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

        $minCrosses = round($width * $height / 2000);

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
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        for ($y = 0; $y < $height; $y++) {
            $sheet->getRowDimension($y + 1)->setRowHeight(static::CELL_SIZE);
        }
        for ($x = 0; $x < $width; $x++) {
            $sheet->getColumnDimensionByColumn($x + 1)->setWidth((int)static::CELL_SIZE / 5);
            $sheet->getColumnDimensionByColumn($x + 1)->setAutoSize(false);
        }

        $marker = new Marker();
        $mapCodeToMarker = [];
        $mapCodeToRgb = [];

        // prepare report
        foreach ($newRgbList as $rgb => $list) {
            if (count($list) === 0) {
                continue;
            }

            $mappedData = $map->getStrictDMCColor($rgb);

            // report
            if (isset($this->report[$mappedData[0]['code']])) {
                $this->report[$mappedData[0]['code']] += count($list);
            } else {
                $this->report[$mappedData[0]['code']] = count($list);
                $mapCodeToRgb[$mappedData[0]['code']] = [
                    'hexRgb' => $mappedData[0]['rgb'],
                    'hexRgb1' => $mappedData[1]['rgb'] ?? null,
                    'hexRgb2' => $mappedData[2]['rgb'] ?? null,
                ];
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
            $newMarker = $marker->next();

            // legend
            $mapCodeToMarker[$mappedData[0]['code']] = $newMarker;

            foreach ($list as [$x, $y]) {
                imagesetpixel($outputImage, $x, $y, $color);
                $sheet->setCellValueByColumnAndRow($x + 1, $y + 1, $newMarker['value']);
                $sheet->getStyleByColumnAndRow($x + 1, $y + 1)->getFont()->getColor()->setRGB($newMarker['fontColor']);
                $sheet->getStyleByColumnAndRow($x + 1, $y + 1)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($newMarker['color']);
                $sheet->getStyleByColumnAndRow($x + 1, $y + 1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyleByColumnAndRow($x + 1, $y + 1)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            }
        }

        // write image
        imagebmp($outputImage, $this->outputFile);

        $legend = $spreadsheet->createSheet();
        $legendRow = 2;

        // preparing report
        $textFile = fopen($this->outputReportFile, 'w');
        asort($this->report);

        $legend->setCellValueByColumnAndRow(1, 1, 'symbol');
        $legend->setCellValueByColumnAndRow(2, 1, 'code');
        $legend->setCellValueByColumnAndRow(3, 1, 'number');
        $legend->setCellValueByColumnAndRow(4, 1, 'rgb');
        $legend->setCellValueByColumnAndRow(5, 1, 'rgb1');
        $legend->setCellValueByColumnAndRow(6, 1, 'rgb2');

        foreach (array_reverse($this->report) as $code => $number) {
            $legend->setCellValueByColumnAndRow(1, $legendRow, $mapCodeToMarker[$code]['value']);
            $legend->getStyleByColumnAndRow(1, $legendRow)->getFont()->getColor()->setRGB($mapCodeToMarker[$code]['fontColor']);
            $legend->getStyleByColumnAndRow(1, $legendRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($mapCodeToMarker[$code]['color']);
            $legend->getStyleByColumnAndRow(1, $legendRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $legend->setCellValueByColumnAndRow(2, $legendRow, $code);
            $legend->setCellValueByColumnAndRow(3, $legendRow, round($number));
            $legend->getStyleByColumnAndRow(4, $legendRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB(Converter::rgbToHexRgb($mapCodeToRgb[$code]['hexRgb']));
            if ($mapCodeToRgb[$code]['hexRgb1']) {
                $legend->getStyleByColumnAndRow(5, $legendRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB(Converter::rgbToHexRgb($mapCodeToRgb[$code]['hexRgb1']));
            }
            if ($mapCodeToRgb[$code]['hexRgb2']) {
                $legend->getStyleByColumnAndRow(6, $legendRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB(Converter::rgbToHexRgb($mapCodeToRgb[$code]['hexRgb2']));
            }
            $legendRow++;

            $code = str_repeat(' ', (30 - strlen($code))) . $code;
            fwrite($textFile, $code . ': ' . round($number) . "\r\n");
        }
        fclose($textFile);

        $legend->getColumnDimensionByColumn(2)->setAutoSize(true);
        $spreadsheet->setActiveSheetIndex(0);

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($this->outputXlsxFile);

        // calc object
        $calc = (new Calculator())->setCanvasSize(20)->setThreadsNumber(2);

        // preparing simple report
        $textFileTwo = fopen($this->outputReportTwoFile, 'w');
        asort($this->simpleReport);
        foreach (array_reverse($this->simpleReport) as $code => $number) {
            $code = str_repeat(' ', (30 - strlen($code))) . $code;
            $number = round($number);
            $number = str_repeat(' ', (7 - strlen($number))) . $number;
            $calcReport = $calc->setCrossCount($number)->calculate();
            fwrite($textFileTwo, $code . ': ' . $number . " (" . $calcReport['length'] . " m, " . $calcReport['pasmas'] . ")\r\n");
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
        $this->outputXlsxFile = $inputFile . '.xlsx';
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
