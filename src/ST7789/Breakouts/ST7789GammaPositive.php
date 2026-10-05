<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts;

use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;
/**
 * PVGAMCTRL (0xE0) — positive-polarity gamma correction.
 *
 * 14 parameter bytes following the datasheet field layout. Several bytes pack
 * two gamma points into their high/low nibbles, so each parameter is emitted
 * as a full byte rather than masked to a single field width.
 */
readonly class ST7789GammaPositive
{
    public function __construct(
        public int $v0_v63 = 0xD0,
        public int $v1 = 0x04,
        public int $v2 = 0x0D,
        public int $v4 = 0x11,
        public int $v6 = 0x13,
        public int $v13_j0 = 0x2B,
        public int $v20 = 0x3F,
        public int $v27_v36 = 0x54,
        public int $v43 = 0x4C,
        public int $v50_j1 = 0x18,
        public int $v57 = 0x0D,
        public int $v59 = 0x0B,
        public int $v61 = 0x1F,
        public int $v62 = 0x23,
    ) {
        foreach (get_object_vars($this) as $field => $value) {
            if (($value < 0) || ($value > 0xFF)) {
                throw ST77xxException::invalidRegisterValue($field, $value, 0, 0xFF);
            }
        }
    }

    /**
     * @return list<int> The 14 parameter bytes in datasheet order.
     */
    public function toBytes(): array
    {
        return array_map(
            static fn (int $value): int => $value & 0xFF,
            [
                $this->v0_v63,
                $this->v1,
                $this->v2,
                $this->v4,
                $this->v6,
                $this->v13_j0,
                $this->v20,
                $this->v27_v36,
                $this->v43,
                $this->v50_j1,
                $this->v57,
                $this->v59,
                $this->v61,
                $this->v62,
            ]
        );
    }

    /**
     * @param  list<int>  $bytes  The 14 parameter bytes in datasheet order.
     */
    public static function fromBytes(array $bytes): static
    {
        return new static(...array_values($bytes));
    }
}
