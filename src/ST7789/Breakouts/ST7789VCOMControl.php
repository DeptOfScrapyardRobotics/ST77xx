<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts;

use GeneralPurposeIO\IntegratedCircuits\DataRegister;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Enums\ST7789VCOMVoltage;

/**
 * VCOMS (0xBB) — VCOM setting, single parameter byte.
 *   bits [5:0] VCOMS — VCOM voltage level
 */
readonly class ST7789VCOMControl extends DataRegister
{
    public function __construct(
        public ST7789VCOMVoltage $vcoms = ST7789VCOMVoltage::V0_725,
    ) {}

    public function toBits(): string
    {
        $vcoms_bits = $this->vcoms->toBits();
        $bits76 = '00';
        $bits543210 = "{$vcoms_bits[5]}{$vcoms_bits[4]}{$vcoms_bits[3]}{$vcoms_bits[2]}{$vcoms_bits[1]}{$vcoms_bits[0]}";

        return "{$bits76}{$bits543210}";
    }

    public static function fromByte(int $byte): static
    {
        $bits = byte2bits($byte);
        $vcoms = bindec("{$bits[5]}{$bits[4]}{$bits[3]}{$bits[2]}{$bits[1]}{$bits[0]}");

        return new static(
            ST7789VCOMVoltage::from($vcoms),
        );
    }

    public static function none(): static
    {
        return new static(
            ST7789VCOMVoltage::from(0x00),
        );
    }
}
