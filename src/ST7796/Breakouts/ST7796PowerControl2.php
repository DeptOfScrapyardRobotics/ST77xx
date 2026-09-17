<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Breakouts;

use GeneralPurposeIO\IntegratedCircuits\DataRegister;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;

/**
 * PWR2 (0xC1) — power control 2, single parameter byte.
 *
 * Selects the VAP(GVDD)/VAN(GVCL) operating amplitude that feeds the source
 * driver. The default 0x06 is the datasheet-recommended setting.
 */
readonly class ST7796PowerControl2 extends DataRegister
{
    /**
     * @throws ST77xxException
     */
    public function __construct(
        public int $amplitude = 0x06,
    ) {
        if (($this->amplitude < 0) || ($this->amplitude > 0xFF)) {
            throw ST77xxException::invalidRegisterValue('amplitude', $this->amplitude, 0, 0xFF);
        }
    }

    public function toBits(): string
    {
        return str_pad(decbin($this->amplitude & 0xFF), 8, '0', STR_PAD_LEFT);
    }

    /**
     * @throws ST77xxException
     */
    public static function fromByte(int $byte): static
    {
        return new static($byte & 0xFF);
    }

    /**
     * @throws ST77xxException
     */
    public static function none(): static
    {
        return new static(0);
    }
}
