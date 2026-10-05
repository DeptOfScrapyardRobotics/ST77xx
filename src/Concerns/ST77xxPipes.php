<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\Concerns;

use DeptOfScrapyardRobotics\Displays\ST77xx\Transports\ST77xxDataTransport;
use GeneralPurposeIO\Contracts\SPI\WritesFromMemory;

/** PipeablePanel for the ST77xx family: RAMWR is 0x2C on the ST7735, ST7789 and ST7796 alike. */
trait ST77xxPipes
{
    abstract public function transport(): ST77xxDataTransport;

    abstract public function setAddressWindow(int $x, int $y, int $width, int $height): void;

    /** CASET and RASET for the window, RAMWR, then DC high: the next bytes on pixelBus() fill the window row by row. */
    public function openWindow(int $x, int $y, int $width, int $height): void
    {
        $this->setAddressWindow($x, $y, $width, $height);
        $this->transport()->command(0x2C);
        $this->transport()->beginData();
    }

    public function pixelBus(): ?WritesFromMemory
    {
        return $this->transport()->memoryBus();
    }
}
