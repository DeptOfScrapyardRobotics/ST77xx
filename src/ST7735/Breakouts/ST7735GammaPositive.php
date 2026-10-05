<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts;

use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;
/**
 * GMCTRP1 (0xE0) — positive-polarity gamma correction.
 * 16 parameter bytes, each a 6-bit value in bits [5:0] (top 2 bits unused).
 * Field order matches the datasheet: VRF0, VOS0, PK0..PK9, SELV0, SELV1, SELV62, SELV63.
 */
readonly class ST7735GammaPositive
{
    public function __construct(
        public int $vrf0 = 0x02,
        public int $vos0 = 0x1C,
        public int $pk0 = 0x07,
        public int $pk1 = 0x12,
        public int $pk2 = 0x37,
        public int $pk3 = 0x32,
        public int $pk4 = 0x29,
        public int $pk5 = 0x2D,
        public int $pk6 = 0x29,
        public int $pk7 = 0x25,
        public int $pk8 = 0x2B,
        public int $pk9 = 0x39,
        public int $selv0 = 0x00,
        public int $selv1 = 0x01,
        public int $selv62 = 0x03,
        public int $selv63 = 0x10,
    ) {
        foreach (get_object_vars($this) as $field => $value) {
            if (($value < 0) || ($value > 0x3F)) {
                throw ST77xxException::invalidRegisterValue($field, $value, 0, 0x3F);
            }
        }
    }

    /**
     * @return list<int> The 16 parameter bytes, each a 6-bit field.
     */
    public function toBytes(): array
    {
        return array_map(
            static fn (int $value): int => $value & 0x3F,
            [
                $this->vrf0,
                $this->vos0,
                $this->pk0,
                $this->pk1,
                $this->pk2,
                $this->pk3,
                $this->pk4,
                $this->pk5,
                $this->pk6,
                $this->pk7,
                $this->pk8,
                $this->pk9,
                $this->selv0,
                $this->selv1,
                $this->selv62,
                $this->selv63,
            ]
        );
    }

    /**
     * @param  list<int>  $bytes  The 16 parameter bytes in datasheet order.
     */
    public static function fromBytes(array $bytes): static
    {
        return new static(...array_values($bytes));
    }
}
