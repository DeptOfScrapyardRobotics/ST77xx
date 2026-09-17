<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Breakouts;

use GeneralPurposeIO\IntegratedCircuits\DataRegister;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Enums\ST7796InversionMode;

/**
 * INVTR (0xB4) — display inversion control, single parameter byte.
 *   bits [2:0] NLA — dot inversion selection.
 */
readonly class ST7796DisplayInversionControl extends DataRegister
{
    public function __construct(
        public ST7796InversionMode $mode = ST7796InversionMode::ONE_DOT,
    ) {}

    public function toBits(): string
    {
        $nla = byte2bits($this->mode->value);

        return "00000{$nla[2]}{$nla[1]}{$nla[0]}";
    }

    public static function fromByte(int $byte): static
    {
        return new static(ST7796InversionMode::from($byte & 0x07));
    }

    public static function none(): static
    {
        return new static(ST7796InversionMode::COLUMN);
    }
}
