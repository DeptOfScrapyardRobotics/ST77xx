<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts;

use GeneralPurposeIO\IntegratedCircuits\DataRegister;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;

/**
 * VDVS (0xC4) — VDV set, single parameter byte.
 *   bits [5:0] VDVS — VDV offset voltage (0x20 == 0V).
 */
readonly class ST7789VDVSet extends DataRegister
{
    public function __construct(
        public int $vdvs = 0x20,
    ) {
        if (($this->vdvs < 0) || ($this->vdvs > 0x3F)) {
            throw ST77xxException::invalidRegisterValue('vdvs', $this->vdvs, 0, 0x3F);
        }
    }

    public function toBits(): string
    {
        $broken_down = byte2bits($this->vdvs);

        return "00{$broken_down[5]}{$broken_down[4]}{$broken_down[3]}{$broken_down[2]}{$broken_down[1]}{$broken_down[0]}";
    }

    public function volts(): float
    {
        return round(($this->vdvs - 0x20) * 0.025, 3);
    }

    public static function fromByte(int $byte): static
    {
        return new static($byte & 0x3F);
    }

    public static function none(): static
    {
        return new static(0);
    }
}
