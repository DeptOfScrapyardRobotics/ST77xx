<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts;

use GeneralPurposeIO\IntegratedCircuits\DataRegister;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;

/**
 * FRCTRL2 (0xC6) — frame rate control in normal mode, single parameter byte.
 *   bits [7:5] NLA — inversion selection in idle/partial mode
 *   bits [4:0] RTNA — frame rate divider (0x0F == 60Hz)
 */
readonly class ST7789FrameRateControl extends DataRegister
{
    /**
     * @throws ST77xxException
     */
    public function __construct(
        public int $rtna = 0x0F,
        public int $inversion_selection = 0x00,
    ) {
        if (($this->rtna < 0) || ($this->rtna > 0x1F)) {
            throw ST77xxException::invalidRegisterValue('rtna', $this->rtna, 0, 0x1F);
        }

        if (($this->inversion_selection < 0) || ($this->inversion_selection > 0x07)) {
            throw ST77xxException::invalidRegisterValue('inversion_selection', $this->inversion_selection, 0, 0x07);
        }
    }

    public function toBits(): string
    {
        $nla = byte2bits($this->inversion_selection);
        $bits765 = "{$nla[2]}{$nla[1]}{$nla[0]}";
        $rtna = byte2bits($this->rtna);
        $bits43210 = "{$rtna[4]}{$rtna[3]}{$rtna[2]}{$rtna[1]}{$rtna[0]}";

        return "{$bits765}{$bits43210}";
    }

    /**
     * @throws ST77xxException
     */
    public static function fromByte(int $byte): static
    {
        $bits = byte2bits($byte);
        $inversion = bindec("{$bits[7]}{$bits[6]}{$bits[5]}");
        $rtna = bindec("{$bits[4]}{$bits[3]}{$bits[2]}{$bits[1]}{$bits[0]}");

        return new static($rtna, $inversion);
    }

    /**
     * @throws ST77xxException
     */
    public static function none(): static
    {
        return new static(0, 0);
    }
}
