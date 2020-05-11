<?php

namespace app\commands;

use app\helpers\Image;
use app\helpers\Map;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     *
     * @param string $message the message to be echoed.
     *
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";

        return ExitCode::OK;
    }

    /**
     * Map rgb
     *
     * @param int $r
     * @param int $g
     * @param int $b
     */
    public function actionMap(int $r, int $g, int $b)
    {
        $map = new Map();
        $result = $map->getDMC($r, $g, $b);

        foreach ($result as $colors) {
            print "\n";
            print "code: " . $colors['code'] . "\n";
            print "name: " . $colors['name'] . "\n";
            print " rgb: " . $colors['rgb'] . "\n";
        }
    }

    /**
     * Prepare cross-stitch
     *
     * @param string $inputFile
     *
     * @throws \Exception
     */
    public function actionPrepareImage(string $inputFile): void
    {
        print "Start cross-stitch\n";

        if (!file_exists($inputFile)) {
            throw new \Exception('File ' . $inputFile . ' is absent');
        }

        print "\tinput file: " . $inputFile . "\n";

        $outputFile = $inputFile . '.new.bmp';
        $outputTextFile = $inputFile . '.new.txt';

        $image = new Image();
        $image->setInputFile($inputFile);
        $image->setOutputFile($outputFile);
        $image->setOutputTextFile($outputTextFile);
        $image->execute();

        print "\toutput file: " . $outputFile . "\n";

        print "End cross-stitch\n";
    }
}
