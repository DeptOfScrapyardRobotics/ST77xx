<?php

use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Enums\ST7735ColorMode;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\ST7735;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\ST7735Configuration;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Enums\ST7789ColorMode;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\ST7789;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\ST7789Configuration;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Enums\ST7796ColorMode;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\ST7796;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\ST7796Configuration;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;
use Surface\Contracts\Framebuffers\BitDepth;

/*
| Every panel runs in 12-, 16- or 18-bit colour and can switch at any time.
| Nothing the package draws favours one depth: fill() packs its colour from
| the panel's current FormatSpec, in whole-pixel chunks.
|
| Test colour: red 0xFF, green 0x80, blue 0x10 — every channel different, so
| a swapped or shifted channel shows.
|   12-bit RGB444, two pixels per three bytes:  F8 1F 81
|   16-bit RGB565, big-endian:                  FC 02
|   18-bit RGB666, channels left-aligned:       FC 80 10
*/

dataset('panels', [
    'ST7735' => [
        fn (int $w, int $h, int $packet) => new ST7735(st77xxWire()[0], new ST7735Configuration(width: $w, height: $h, max_packet_size: $packet)),
        [12 => ST7735ColorMode::COLOR12, 16 => ST7735ColorMode::COLOR16, 18 => ST7735ColorMode::COLOR18],
        [12 => 0x03, 16 => 0x05, 18 => 0x06],
    ],
    'ST7789' => [
        fn (int $w, int $h, int $packet) => new ST7789(st77xxWire()[0], new ST7789Configuration(width: $w, height: $h, max_packet_size: $packet)),
        [12 => ST7789ColorMode::COLOR12, 16 => ST7789ColorMode::COLOR16, 18 => ST7789ColorMode::COLOR18],
        [12 => 0x53, 16 => 0x55, 18 => 0x66],
    ],
    'ST7796' => [
        fn (int $w, int $h, int $packet) => new ST7796(st77xxWire()[0], new ST7796Configuration(width: $w, height: $h, max_packet_size: $packet)),
        [12 => ST7796ColorMode::COLOR12, 16 => ST7796ColorMode::COLOR16, 18 => ST7796ColorMode::COLOR18],
        [12 => 0x53, 16 => 0x55, 18 => 0x66],
    ],
]);

/** The fake SPI bus behind a panel built by the dataset closures. */
function spiOf(object $panel): \DeptOfScrapyardRobotics\Displays\ST77xx\Tests\Support\FakeSPITransport
{
    return (fn () => $this->transport)->call($panel->transport());
}

$fills = [
    12 => [[0xF8, 0x1F, 0x81], [[0xF8, 0x1F, 0x81], [0xF8, 0x1F, 0x81], [0xF8, 0x1F, 0x81]]],
    16 => [[0xFC, 0x02], [[0xFC, 0x02, 0xFC, 0x02], [0xFC, 0x02, 0xFC, 0x02], [0xFC, 0x02, 0xFC, 0x02]]],
    18 => [[0xFC, 0x80, 0x10], array_fill(0, 6, [0xFC, 0x80, 0x10])],
];

foreach ($fills as $bits => [$pixel, $chunks]) {
    it("fills in {$bits}-bit colour with whole-pixel packets", function (Closure $make, array $modes, array $colmod) use ($bits, $chunks): void {
        $panel = $make(3, 2, 4);
        $spi = spiOf($panel);

        $panel->color_mode = $modes[$bits];
        $before = count($spi->writes);
        $panel->fill(0xFF, 0x80, 0x10);

        expect($spi->commands($before - 2))->toBe([[0x3A, [$colmod[$bits]]], ...array_slice($spi->commands($before - 2), 1)])
            ->and($panel->formatSpec()->bit_depth)->toBe(BitDepth::from($bits))
            ->and(array_slice($spi->writes, $before))->toBe([
                ['cmd', [0x2A]], ['data', [0x00, 0x00, 0x00, 0x02]],
                ['cmd', [0x2B]], ['data', [0x00, 0x00, 0x00, 0x01]],
                ['cmd', [0x2C]],
                ...array_map(fn (array $chunk): array => ['data', $chunk], $chunks),
            ]);
    })->with('panels');
}

it('switches colour mode at will, and every fill follows the mode in force', function (Closure $make, array $modes, array $colmod): void {
    $panel = $make(2, 1, 64);
    $spi = spiOf($panel);
    $before = count($spi->writes);

    foreach ([18, 12, 16, 18] as $bits) {
        $panel->color_mode = $modes[$bits];
        $panel->fill(0xFF, 0x80, 0x10);
    }

    $colmods = array_values(array_filter($spi->commands($before), fn (array $c): bool => $c[0] === 0x3A));
    $pixels = array_values(array_filter($spi->commands($before), fn (array $c): bool => $c[0] === 0x2C));

    expect(array_column($colmods, 1))->toBe([[$colmod[18]], [$colmod[12]], [$colmod[16]], [$colmod[18]]])
        ->and(array_column($pixels, 1))->toBe([
            [0xFC, 0x80, 0x10, 0xFC, 0x80, 0x10],
            [0xF8, 0x1F, 0x81],
            [0xFC, 0x02, 0xFC, 0x02],
            [0xFC, 0x80, 0x10, 0xFC, 0x80, 0x10],
        ])
        ->and($panel->formatSpec()->bit_depth)->toBe(BitDepth::B18);
})->with('panels');

it('accepts the colour mode as a bit depth too', function (Closure $make, array $modes, array $colmod): void {
    $panel = $make(1, 1, 64);

    foreach ([12, 16, 18] as $bits) {
        $panel->setPixelFormat($bits);

        expect($panel->color_mode)->toBe($modes[$bits])
            ->and($panel->formatSpec()->bit_depth->value)->toBe($bits);
    }
})->with('panels');

it('pads an odd pixel count to a whole 12-bit pair', function (Closure $make, array $modes): void {
    $panel = $make(3, 1, 64);
    $spi = spiOf($panel);
    $panel->color_mode = $modes[12];
    $before = count($spi->writes);

    $panel->fill(0x00, 0x00, 0xFF);

    expect(end($spi->writes))->toBe(['data', [0x00, 0xF0, 0x0F, 0x00, 0xF0, 0x0F]])
        ->and(count($spi->writes) - $before)->toBe(6);
})->with('panels');

it('still sends every byte, in order, when the packet is smaller than a pixel', function (Closure $make, array $modes): void {
    $panel = $make(2, 1, 2);
    $spi = spiOf($panel);
    $panel->color_mode = $modes[18];
    $before = count($spi->writes);

    $panel->fill(0x04, 0x08, 0x0C);

    $data = array_values(array_filter(array_slice($spi->writes, $before + 5), fn (array $w): bool => $w[0] === 'data'));

    expect(array_merge(...array_column($data, 1)))->toBe([0x04, 0x08, 0x0C, 0x04, 0x08, 0x0C]);
})->with('panels');

it('scales each 8-bit channel to the depth in force', function (Closure $make, array $modes): void {
    $panel = $make(1, 1, 64);
    $spi = spiOf($panel);
    $pixel = function () use ($panel, $spi): array {
        $panel->fill(0x12, 0x34, 0x56);

        return end($spi->writes)[1];
    };

    $panel->color_mode = $modes[16];
    $rgb565 = $pixel();
    $panel->color_mode = $modes[18];
    $rgb666 = $pixel();
    $panel->color_mode = $modes[12];
    $rgb444 = $pixel();

    expect($rgb565)->toBe([(0x12 >> 3) << 3 | (0x34 >> 5), ((0x34 >> 2) & 0x07) << 5 | (0x56 >> 3)])
        ->and($rgb666)->toBe([0x10, 0x34, 0x54])
        ->and($rgb444)->toBe([0x13, 0x51, 0x35]);
})->with('panels');

it('rejects a colour channel outside 0 to 255', function (Closure $make): void {
    $panel = $make(1, 1, 64);

    expect(fn () => $panel->fill(256, 0, 0))->toThrow(ST77xxException::class, 'red')
        ->and(fn () => $panel->fill(0, -1, 0))->toThrow(ST77xxException::class, 'green')
        ->and(fn () => $panel->fill(0, 0, 300))->toThrow(ST77xxException::class, 'blue');
})->with('panels');
