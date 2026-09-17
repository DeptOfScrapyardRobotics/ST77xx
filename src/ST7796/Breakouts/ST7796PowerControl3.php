<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Breakouts;

use GeneralPurposeIO\IntegratedCircuits\DataRegister;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;

/**
 * PWR3 (0xC2) — power control 3, single parameter byte.
 *
 * Configures the operating frequency / drive current of the step-up circuits.
 * The default 0xA7 is the datasheet-recommended setting.
 */
readonly class ST7796PowerControl3 extends DataRegister
{
    /**
     * @throws ST77xxException
     */
    public function __construct(
        public int $source_drive = 0xA7,
    ) {
        if (($this->source_drive < 0) || ($this->source_drive > 0xFF)) {
            throw ST77xxException::invalidRegisterValue('source_drive', $this->source_drive, 0, 0xFF);
        }
    }

    public function toBits(): string
    {
        return str_pad(decbin($this->source_drive & 0xFF), 8, '0', STR_PAD_LEFT);
    }

    /**
     * @throws ST77xxException
     */
    public static function fromByte(int $byte): static
    {
        return new static($byte & 0xFF);
    }

    /**
     * @throws ST77xxException
     */
    public static function none(): static
    {
        return new static(0);
    }
}
