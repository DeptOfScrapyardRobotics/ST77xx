<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts;

use GeneralPurposeIO\IntegratedCircuits\DataRegister;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;

readonly class ST7735ClockCycle extends DataRegister
{
    /**
     * @throws ST77xxException
     */
    public function __construct(
        public int $osc_clock_cycles_per_line = 0x02
    ) {
        if (($this->osc_clock_cycles_per_line < 0) || ($this->osc_clock_cycles_per_line > 15)) {
            throw ST77xxException::invalidRegisterValue('osc_clock_cycles_per_line', $this->osc_clock_cycles_per_line, 0, 15);
        }
    }

    public function toBits(): string
    {
        $bits7654 = '0000';
        $broken_down = byte2bits($this->osc_clock_cycles_per_line);
        $bits3210 = "{$broken_down[3]}{$broken_down[2]}{$broken_down[1]}{$broken_down[0]}";

        return "{$bits7654}{$bits3210}";
    }

    /**
     * @throws ST77xxException
     */
    public static function fromByte(int $byte): static
    {
        return new static($byte);
    }

    /**
     * @throws ST77xxException
     */
    public static function none(): static
    {
        return new static(0);
    }
}
