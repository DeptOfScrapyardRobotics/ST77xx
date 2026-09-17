<?php

use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735MADControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Enums\ST7735ColorMode;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\ST7735;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\ST7735Configuration;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;
use DeptOfScrapyardRobotics\Displays\ST77xx\Tests\Support\FakeSPITransport;
use GeneralPurposeIO\Contracts\IntegratedCircuits\DisplayPanel;
use Surface\Contracts\Framebuffers\BitDepth;
use Surface\Contracts\Framebuffers\PixelFormat;

/** @return array{0: ST7735, 1: FakeSPITransport} */
function st7735(?ST7735Configuration $config = null, bool $boot = true): array
{
    [$transport, $spi] = st77xxWire();

    return [new ST7735($transport, $config ?? new ST7735Configuration, boot_now: $boot), $spi];
}

it('is a 128×128 display panel by default', function (): void {
    [$panel] = st7735(boot: false);

    expect($panel)->toBeInstanceOf(DisplayPanel::class)
        ->and([$panel->width(), $panel->height()])->toBe([128, 128])
        ->and($panel->formatSpec()->pixel_format)->toBe(PixelFormat::ROW_MAJOR)
        ->and($panel->formatSpec()->bit_depth)->toBe(BitDepth::B16);
});

it('boots with the ST7735R init sequence', function (): void {
    [$panel, $spi] = st7735();

    expect($panel->hasBooted())->toBeTrue()
        ->and($spi->commands())->toBe([
            [0x01, []],
            [0x11, []],
            [0xB1, [0x01, 0x2C, 0x2D]],
            [0xB2, [0x01, 0x2C, 0x2D]],
            [0xB3, [0x01, 0x2C, 0x2D, 0x01, 0x2C, 0x2D]],
            [0xB4, [0x07]],
            [0xC0, [0xA2, 0x02, 0x84]],
            [0xC1, [0xC5]],
            [0xC2, [0x0A, 0x00]],
            [0xC3, [0x8A, 0x2A]],
            [0xC4, [0x8A, 0xEE]],
            [0xC5, [0x0E]],
            [0x20, []],
            [0x36, [0xC8]],
            [0x3A, [0x05]],
            [0xE0, [0x02, 0x1C, 0x07, 0x12, 0x37, 0x32, 0x29, 0x2D, 0x29, 0x25, 0x2B, 0x39, 0x00, 0x01, 0x03, 0x10]],
            [0xE1, [0x03, 0x1D, 0x07, 0x06, 0x2E, 0x2C, 0x29, 0x2D, 0x2E, 0x2E, 0x37, 0x3F, 0x00, 0x00, 0x02, 0x10]],
            [0x13, []],
            [0x29, []],
        ]);
});

it('boots inverted, with the configured orientation and colour mode', function (): void {
    [, $spi] = st7735(new ST7735Configuration(
        invert_display: true,
        mad_ctrl: new ST7735MADControl(false, false, false, false, false, false),
        color_mode: ST7735ColorMode::COLOR18,
    ));

    expect($spi->commands())->toContain([0x21, []], [0x36, [0x00]], [0x3A, [0x06]])
        ->and($spi->commands())->not->toContain([0x20, []]);
});

it('opens a window with the panel offsets applied, then writes RAM', function (): void {
    [$panel, $spi] = st7735(new ST7735Configuration(x_offset: 2, y_offset: 3));
    $before = count($spi->writes);

    $panel->transmit(0, 0, [0x00, 0x1F]);

    expect($spi->commands($before))->toBe([...st77xxWindow(2, 3, 129, 130), [0x2C, [0x00, 0x1F]]]);
});

it('reads and writes settings through properties', function (): void {
    [$panel, $spi] = st7735();
    $before = count($spi->writes);

    $panel->invert_display = true;
    $panel->display_on = false;
    $panel->color_mode = ST7735ColorMode::COLOR12;

    expect($spi->commands($before))->toBe([[0x21, []], [0x28, []], [0x3A, [0x03]]])
        ->and($panel->invert_display)->toBeTrue()
        ->and($panel->display_on)->toBeFalse()
        ->and($panel->formatSpec()->bit_depth)->toBe(BitDepth::B12)
        ->and($panel->nfc->toBytes())->toBe([0x01, 0x2C, 0x2D])
        ->and(fn () => $panel->nope)->toThrow(ST77xxException::class, "Invalid property 'nope'");
});

it('rejects out-of-range frame-rate values with the shared register exception', function (): void {
    expect(fn () => new \DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735ClockCycle(16))
        ->toThrow(ST77xxException::class, 'osc_clock_cycles_per_line')
        ->and(fn () => new \DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735PorchLines(64))
        ->toThrow(ST77xxException::class, 'blank_lines_to_insert');
});
