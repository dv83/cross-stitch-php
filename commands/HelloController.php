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
        $result = $map->getDMCColor($r, $g, $b);

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
     * @param bool $chessMode
     * @param bool $mixedMode
     *
     * @throws \Exception
     */
    public function actionPrepareImage(string $inputFile, bool $chessMode = false, bool $mixedMode = false): void
    {
        print "Start cross-stitch\n";

        if (!file_exists($inputFile)) {
            throw new \Exception('File ' . $inputFile . ' is absent');
        }

        print "\tinput file: " . $inputFile . "\n";

        print "\tchess mode: " . ($chessMode ? 'TRUE' : 'FALSE') . "\n";
        print "\tmixed mode: " . ($mixedMode ? 'TRUE' : 'FALSE') . "\n";

        $image = new Image();
        $image->setInputFile($inputFile);
        $image->setChessMode($chessMode);
        $image->setMixedMode($mixedMode);
        $image->execute();

        print "\toutput file: " . $inputFile . ".new.bmp\n";

        print "End cross-stitch\n";
    }
}
