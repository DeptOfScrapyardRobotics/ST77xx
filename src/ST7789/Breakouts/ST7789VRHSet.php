<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts;

use GeneralPurposeIO\IntegratedCircuits\DataRegister;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;

/**
 * VRHS (0xC3) — VRH set, single parameter byte.
 *   bits [5:0] VRHS — drives the VAP/VAN reference amplitude.
 */
readonly class ST7789VRHSet extends DataRegister
{
    public function __construct(
        public int $vrhs = 0x12,
    ) {
        if (($this->vrhs < 0) || ($this->vrhs > 0x3F)) {
            throw ST77xxException::invalidRegisterValue('vrhs', $this->vrhs, 0, 0x3F);
        }
    }

    public function toBits(): string
    {
        $broken_down = byte2bits($this->vrhs);

        return "00{$broken_down[5]}{$broken_down[4]}{$broken_down[3]}{$broken_down[2]}{$broken_down[1]}{$broken_down[0]}";
    }

    public function volts(): float
    {
        return round(3.55 + 0.05 * $this->vrhs, 3);
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
