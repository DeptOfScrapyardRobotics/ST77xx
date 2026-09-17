<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Breakouts;

use GeneralPurposeIO\IntegratedCircuits\DataRegister;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;

/**
 * VCMPCTL (0xC5) — VCOM control, single parameter byte.
 *   bits [5:0] VCM — VCOM voltage level (0x18 == datasheet default).
 */
readonly class ST7796VCOMControl extends DataRegister
{
    /**
     * @throws ST77xxException
     */
    public function __construct(
        public int $vcm = 0x18,
    ) {
        if (($this->vcm < 0) || ($this->vcm > 0x3F)) {
            throw ST77xxException::invalidRegisterValue('vcm', $this->vcm, 0, 0x3F);
        }
    }

    public function toBits(): string
    {
        $broken_down = byte2bits($this->vcm);

        return "00{$broken_down[5]}{$broken_down[4]}{$broken_down[3]}{$broken_down[2]}{$broken_down[1]}{$broken_down[0]}";
    }

    /**
     * @throws ST77xxException
     */
    public static function fromByte(int $byte): static
    {
        return new static($byte & 0x3F);
    }

    /**
     * @throws ST77xxException
     */
    public static function none(): static
    {
        return new static(0);
    }
}
