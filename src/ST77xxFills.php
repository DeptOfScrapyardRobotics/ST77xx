<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx;

use DeptOfScrapyardRobotics\Displays\ST77xx\Transports\ST77xxDataTransport;
use Surface\Contracts\Framebuffers\BitDepth;
use Surface\Contracts\Framebuffers\FormatSpec;

/**
 * Solid fill straight to panel RAM, packed for whatever colour mode the panel
 * is in right now. The colour is given as 8-bit channels and scaled down:
 *   12-bit  RGB444, two pixels per three bytes (an odd count is padded)
 *   16-bit  RGB565, high byte first
 *   18-bit  RGB666, each channel left-aligned in its byte
 * Data goes out in chunks of whole pixels no larger than max_packet_size.
 */
trait ST77xxFills
{
    abstract public function transport(): ST77xxDataTransport;

    abstract public function formatSpec(): FormatSpec;

    abstract public function width(): int;

    abstract public function height(): int;

    abstract public function setAddressWindow(int $x, int $y, int $width, int $height): void;

    /**
     * @throws ST77xxException
     */
    public function fill(int $red, int $green, int $blue): void
    {
        foreach (['red' => $red, 'green' => $green, 'blue' => $blue] as $channel => $value) {
            if (($value < 0) || ($value > 0xFF)) {
                throw ST77xxException::invalidRegisterValue($channel, $value, 0, 0xFF);
            }
        }

        $depth = $this->formatSpec()->bit_depth;
        $unit = static::encodeFillUnit($depth, $red, $green, $blue);
        $pixels_per_unit = $depth === BitDepth::B12 ? 2 : 1;
        $units = intdiv($this->width() * $this->height() + $pixels_per_unit - 1, $pixels_per_unit);
        $units_per_chunk = max(1, intdiv($this->config()->get('max_packet_size'), count($unit)));

        $this->setAddressWindow(0, 0, $this->width(), $this->height());
        $this->transport()->command(0x2C);

        $chunk = array_merge(...array_fill(0, $units_per_chunk, $unit));

        while ($units > 0) {
            $count = min($units, $units_per_chunk);
            $this->transport()->data($count === $units_per_chunk ? $chunk : array_slice($chunk, 0, $count * count($unit)));
            $units -= $count;
        }
    }

    /**
     * The repeating byte group for one solid colour: one pixel, or a pixel pair at 12-bit.
     *
     * @return list<int>
     */
    protected static function encodeFillUnit(BitDepth $depth, int $red, int $green, int $blue): array
    {
        return match ($depth) {
            BitDepth::B12 => (function () use ($red, $green, $blue): array {
                [$r, $g, $b] = [$red >> 4, $green >> 4, $blue >> 4];

                return [($r << 4) | $g, ($b << 4) | $r, ($g << 4) | $b];
            })(),
            BitDepth::B16 => (function () use ($red, $green, $blue): array {
                $word = (($red >> 3) << 11) | (($green >> 2) << 5) | ($blue >> 3);

                return [$word >> 8, $word & 0xFF];
            })(),
            BitDepth::B18 => [$red & 0xFC, $green & 0xFC, $blue & 0xFC],
            default => throw ST77xxException::invalidColorMode($depth->value),
        };
    }
}
