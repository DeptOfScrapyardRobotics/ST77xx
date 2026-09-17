<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts;

use GeneralPurposeIO\IntegratedCircuits\DataRegister;

readonly class ST7735MADControl extends DataRegister
{
    public function __construct(
        public bool $bottom_top_row_addresses = true,
        public bool $right_left_column_addresses = true,
        public bool $pixel_direction_vertical = false,
        public bool $bottom_top_refresh = false,
        public bool $bgr_order_mode = true,
        public bool $right_left_refresh = false,
    ) {}

    public function toBits(): string
    {
        $bit7 = $this->bottom_top_row_addresses ? '1' : '0';
        $bit6 = $this->right_left_column_addresses ? '1' : '0';
        $bit5 = $this->pixel_direction_vertical ? '1' : '0';
        $bit4 = $this->bottom_top_refresh ? '1' : '0';
        $bit3 = $this->bgr_order_mode ? '1' : '0';
        $bit2 = $this->right_left_refresh ? '1' : '0';
        $bits10 = '00';

        return "{$bit7}{$bit6}{$bit5}{$bit4}{$bit3}{$bit2}{$bits10}";
    }

    public static function fromByte(int $byte): static
    {
        $bits = byte2bits($byte);

        return new static(
            $bits[7],
            $bits[6],
            $bits[5],
            $bits[4],
            $bits[3],
            $bits[2],
        );
    }

    public static function none(): static
    {
        return new static(
            false,
            false,
            false,
            false,
            false,
            false,
        );
    }
}
