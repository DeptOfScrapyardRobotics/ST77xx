<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts;

use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;

/**
 * PORCTRL (0xB2) — porch setting.
 *
 * 5 parameter bytes:
 *   1. BPA[6:0]   back porch in normal mode
 *   2. FPA[6:0]   front porch in normal mode
 *   3. PSEN       separate porch control enable (bit 0)
 *   4. FPB[3:0] / BPB[3:0]  front/back porch in separate (partial) mode
 *   5. FPC[3:0] / BPC[3:0]  front/back porch in separate (idle) mode
 */
readonly class ST7789PorchControl
{
    public function __construct(
        public int $back_porch_normal = 0x0C,
        public int $front_porch_normal = 0x0C,
        public bool $separate_porch_enabled = false,
        public int $front_porch_separate = 0x03,
        public int $back_porch_separate = 0x03,
        public int $front_porch_idle = 0x03,
        public int $back_porch_idle = 0x03,
    ) {
        $this->assertSevenBit($this->back_porch_normal, 'back_porch_normal');
        $this->assertSevenBit($this->front_porch_normal, 'front_porch_normal');
        $this->assertNibble($this->front_porch_separate, 'front_porch_separate');
        $this->assertNibble($this->back_porch_separate, 'back_porch_separate');
        $this->assertNibble($this->front_porch_idle, 'front_porch_idle');
        $this->assertNibble($this->back_porch_idle, 'back_porch_idle');
    }

    private function assertSevenBit(int $value, string $field): void
    {
        if (($value < 0) || ($value > 0x7F)) {
            throw ST77xxException::invalidRegisterValue($field, $value, 0, 0x7F);
        }
    }

    private function assertNibble(int $value, string $field): void
    {
        if (($value < 0) || ($value > 0x0F)) {
            throw ST77xxException::invalidRegisterValue($field, $value, 0, 0x0F);
        }
    }

    /**
     * @return list<int>
     */
    public function toBytes(): array
    {
        return [
            $this->back_porch_normal & 0x7F,
            $this->front_porch_normal & 0x7F,
            $this->separate_porch_enabled ? 0x01 : 0x00,
            (($this->front_porch_separate & 0x0F) << 4) | ($this->back_porch_separate & 0x0F),
            (($this->front_porch_idle & 0x0F) << 4) | ($this->back_porch_idle & 0x0F),
        ];
    }

    public static function fromBytes(
        int $back_porch_normal = 0x0C,
        int $front_porch_normal = 0x0C,
        bool $separate_porch_enabled = false,
        int $front_porch_separate = 0x03,
        int $back_porch_separate = 0x03,
        int $front_porch_idle = 0x03,
        int $back_porch_idle = 0x03,
    ): static {
        return new static(
            $back_porch_normal,
            $front_porch_normal,
            $separate_porch_enabled,
            $front_porch_separate,
            $back_porch_separate,
            $front_porch_idle,
            $back_porch_idle,
        );
    }
}
