<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts;

use GeneralPurposeIO\IntegratedCircuits\DataRegister;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Enums\ST7735InternalVGHVoltage;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Enums\ST7735VGHGateDriveVoltageHigh;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Enums\ST7735VGLGateDriveVoltageLow;

readonly class ST7735PowerControl2 extends DataRegister
{
    public function __construct(
        public ST7735InternalVGHVoltage $vgh25 = ST7735InternalVGHVoltage::V2_4,
        public ST7735VGLGateDriveVoltageLow $vgl_sel = ST7735VGLGateDriveVoltageLow::N10_0,
        public ST7735VGHGateDriveVoltageHigh $vgh_bt = ST7735VGHGateDriveVoltageHigh::TRIPLE_AVDD_MINUS_HALF,
    ) {}

    public function toBits(): string
    {
        $vgh25_bits = $this->vgh25->toBits();
        $bits76 = "{$vgh25_bits[1]}{$vgh25_bits[0]}";
        $bits54 = '00';
        $vgl_sel_bits = $this->vgl_sel->toBits();
        $bits32 = "{$vgl_sel_bits[1]}{$vgl_sel_bits[0]}";
        $vgh_bt_bits = $this->vgh_bt->toBits();
        $bits10 = "{$vgh_bt_bits[1]}{$vgh_bt_bits[0]}";

        return "{$bits76}{$bits54}{$bits32}{$bits10}";
    }

    public static function fromByte(int $byte): static
    {
        $bits = byte2bits($byte);
        $vgh25_bits = bindec("{$bits[7]}{$bits[6]}");
        $vgl_sel_bits = bindec("{$bits[3]}{$bits[2]}");
        $vgh_bt_bits = bindec("{$bits[1]}{$bits[0]}");

        return new static(
            ST7735InternalVGHVoltage::from($vgh25_bits),
            ST7735VGLGateDriveVoltageLow::from($vgl_sel_bits),
            ST7735VGHGateDriveVoltageHigh::from($vgh_bt_bits)
        );
    }

    public static function none(): static
    {
        return new static(
            ST7735InternalVGHVoltage::from(0x00),
            ST7735VGLGateDriveVoltageLow::from(0x00),
            ST7735VGHGateDriveVoltageHigh::from(0x00)
        );
    }
}
