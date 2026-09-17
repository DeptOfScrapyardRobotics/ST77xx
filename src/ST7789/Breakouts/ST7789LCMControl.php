<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts;

use GeneralPurposeIO\IntegratedCircuits\DataRegister;

/**
 * LCMCTRL (0xC0) — LCM control, single parameter byte.
 *
 * Each bit conditionally inverts a corresponding MADCTL behaviour:
 *   bit 6 XMY  — inverse the MY (row address order)
 *   bit 5 XBGR — inverse the RGB/BGR order
 *   bit 4 XINV — inverse the display data
 *   bit 3 XMX  — inverse the MX (column address order)
 *   bit 2 XMH  — inverse the horizontal refresh order
 *   bit 1 XMV  — inverse the MV (row/column exchange)
 *   bit 0 XGS  — inverse the gate scan order
 */
readonly class ST7789LCMControl extends DataRegister
{
    public function __construct(
        public bool $invert_row_order = false,
        public bool $invert_bgr_order = true,
        public bool $invert_display_data = false,
        public bool $invert_column_order = true,
        public bool $invert_horizontal_refresh = true,
        public bool $invert_row_column_exchange = false,
        public bool $invert_gate_scan = false,
    ) {}

    public function toBits(): string
    {
        $bit7 = '0';
        $bit6 = $this->invert_row_order ? '1' : '0';
        $bit5 = $this->invert_bgr_order ? '1' : '0';
        $bit4 = $this->invert_display_data ? '1' : '0';
        $bit3 = $this->invert_column_order ? '1' : '0';
        $bit2 = $this->invert_horizontal_refresh ? '1' : '0';
        $bit1 = $this->invert_row_column_exchange ? '1' : '0';
        $bit0 = $this->invert_gate_scan ? '1' : '0';

        return "{$bit7}{$bit6}{$bit5}{$bit4}{$bit3}{$bit2}{$bit1}{$bit0}";
    }

    public static function fromByte(int $byte): static
    {
        $bits = byte2bits($byte);

        return new static(
            (bool) $bits[6],
            (bool) $bits[5],
            (bool) $bits[4],
            (bool) $bits[3],
            (bool) $bits[2],
            (bool) $bits[1],
            (bool) $bits[0],
        );
    }

    public static function none(): static
    {
        return new static(false, false, false, false, false, false, false);
    }
}
