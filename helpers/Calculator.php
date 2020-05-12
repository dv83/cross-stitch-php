<?php

namespace app\helpers;

/**
 * Class Calculator
 *
 * @author Dmitry Mitko <dima@icecat.biz>
 */
class Calculator
{
    protected const CANVAS_SIZES = [11, 14, 16, 18, 20, 22];

    /**
     * @var int|null
     */
    protected ?int $crossCount;

    /**
     * @var int|null
     */
    protected ?int $canvasSize;

    /**
     * @var int|null
     */
    protected ?int $threadsNumber;

    /**
     * Calculate the
     *
     * @return array
     */
    public function calculate(): array
    {
        if ($this->crossCount === null || $this->crossCount <= 0 || $this->canvasSize === null || $this->threadsNumber === null) {
            return [
                'length' => 0,
                'pasmas' => 0,
            ];
        }

        $length = 0;
        if ($this->canvasSize === 11) {
            $length = $this->crossCount / 396;
        } elseif ($this->canvasSize === 14) {
            $length = $this->crossCount / 510;
        } elseif ($this->canvasSize === 16) {
            $length = $this->crossCount / 590;
        } elseif ($this->canvasSize === 18) {
            $length = $this->crossCount / 681;
        } elseif ($this->canvasSize === 20) {
            $length = $this->crossCount / 786;
        } elseif ($this->canvasSize === 22) {
            $length = $this->crossCount / 912;
        }

        $length *= $this->threadsNumber;

        $length = round($length * 10) / 10;

        if ($length === 0) {
            $length = 0.1;
        }

        return [
            'length' => $length,
            'pasmas' => (int)(round($length / 8 + 1)),
        ];
    }

    /**
     * Set crosses count
     *
     * @param int $crossCount
     *
     * @return Calculator
     */
    public function setCrossCount(int $crossCount): Calculator
    {
        $this->crossCount = $crossCount <= 0 ? null : $crossCount;

        return $this;
    }

    /**
     * Set canvas size
     *
     * @param int $canvasSize
     *
     * @return Calculator
     */
    public function setCanvasSize(int $canvasSize): Calculator
    {
        $this->canvasSize = !in_array($canvasSize, static::CANVAS_SIZES) ? null : $canvasSize;

        return $this;
    }

    /**
     * Set number of threads
     *
     * @param int $threadsNumber
     *
     * @return Calculator
     */
    public function setThreadsNumber(int $threadsNumber): Calculator
    {
        $this->threadsNumber = $threadsNumber <= 0 || $threadsNumber > 3 ? null : $threadsNumber;

        return $this;
    }
}
