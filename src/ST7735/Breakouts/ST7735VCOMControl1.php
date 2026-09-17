<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts;

use GeneralPurposeIO\IntegratedCircuits\DataRegister;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Enums\ST7735VCOMVoltage;

readonly class ST7735VCOMControl1 extends DataRegister
{
    public function __construct(
        public ST7735VCOMVoltage $vcoms = ST7735VCOMVoltage::VN0_775,
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
            ST7735VCOMVoltage::from($vcoms),
        );
    }

    public static function none(): static
    {
        return new static(
            ST7735VCOMVoltage::from(0x00),
        );
    }
}
