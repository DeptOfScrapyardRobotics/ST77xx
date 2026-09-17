<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts;

use GeneralPurposeIO\IntegratedCircuits\DataRegister;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;

readonly class ST7735PorchLines extends DataRegister
{
    public function __construct(
        public int $blank_lines_to_insert = 0x2C
    ) {
        if (($this->blank_lines_to_insert < 0) || ($this->blank_lines_to_insert > 63)) {
            throw ST77xxException::invalidRegisterValue('blank_lines_to_insert', $this->blank_lines_to_insert, 0, 63);
        }
    }

    public function toBits(): string
    {
        $bits76 = '00';
        $broken_down = byte2bits($this->blank_lines_to_insert);
        $bits543210 = "{$broken_down[5]}{$broken_down[4]}{$broken_down[3]}{$broken_down[2]}{$broken_down[1]}{$broken_down[0]}";

        return "{$bits76}{$bits543210}";
    }

    public static function fromByte(int $byte): static
    {
        return new static($byte);
    }

    public static function none(): static
    {
        return new static(0);
    }
}
