<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts;

use GeneralPurposeIO\IntegratedCircuits\DataRegister;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Enums\ST7789GateHighVoltage;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Enums\ST7789GateLowVoltage;

/**
 * GCTRL (0xB7) — gate control, single parameter byte.
 *   bits [6:4] VGHS — gate high voltage
 *   bits [2:0] VGLS — gate low voltage
 */
readonly class ST7789GateControl extends DataRegister
{
    public function __construct(
        public ST7789GateHighVoltage $vghs = ST7789GateHighVoltage::V13_26,
        public ST7789GateLowVoltage $vgls = ST7789GateLowVoltage::VN10_43,
    ) {}

    public function toBits(): string
    {
        $vghs_bits = $this->vghs->toBits();
        $bits654 = "{$vghs_bits[2]}{$vghs_bits[1]}{$vghs_bits[0]}";
        $vgls_bits = $this->vgls->toBits();
        $bits210 = "{$vgls_bits[2]}{$vgls_bits[1]}{$vgls_bits[0]}";

        return "0{$bits654}0{$bits210}";
    }

    public static function fromByte(int $byte): static
    {
        $bits = byte2bits($byte);
        $vghs = bindec("{$bits[6]}{$bits[5]}{$bits[4]}");
        $vgls = bindec("{$bits[2]}{$bits[1]}{$bits[0]}");

        return new static(
            ST7789GateHighVoltage::from($vghs),
            ST7789GateLowVoltage::from($vgls),
        );
    }

    public static function none(): static
    {
        return new static(
            ST7789GateHighVoltage::from(0x00),
            ST7789GateLowVoltage::from(0x00),
        );
    }
}
