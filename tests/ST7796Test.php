<?php

use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Breakouts\ST7796MADControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Enums\ST7796ColorMode;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\ST7796;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\ST7796Configuration;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;
use DeptOfScrapyardRobotics\Displays\ST77xx\Tests\Support\FakeSPITransport;
use GeneralPurposeIO\Contracts\IntegratedCircuits\DisplayPanel;
use Surface\Contracts\Framebuffers\BitDepth;

/** @return array{0: ST7796, 1: FakeSPITransport} */
function st7796(?ST7796Configuration $config = null, bool $boot = true): array
{
    [$transport, $spi] = st77xxWire();

    return [new ST7796($transport, $config ?? new ST7796Configuration, boot_now: $boot), $spi];
}

it('is a 480×320 display panel by default', function (): void {
    [$panel] = st7796(boot: false);

    expect($panel)->toBeInstanceOf(DisplayPanel::class)
        ->and([$panel->width(), $panel->height()])->toBe([480, 320])
        ->and($panel->formatSpec()->bit_depth)->toBe(BitDepth::B16);
});

it('boots with the ST7796S init sequence, extended commands unlocked and locked again', function (): void {
    [$panel, $spi] = st7796();

    expect($panel->hasBooted())->toBeTrue()
        ->and($spi->commands())->toBe([
            [0x28, []],
            [0x11, []],
            [0xF0, [0xC3]],
            [0xF0, [0x96]],
            [0x36, [0x28]],
            [0x3A, [0x55]],
            [0xB4, [0x01]],
            [0xB6, [0x80, 0x02, 0x3B]],
            [0xE8, [0x40, 0x8A, 0x00, 0x00, 0x29, 0x19, 0xA5, 0x33]],
            [0xC1, [0x06]],
            [0xC2, [0xA7]],
            [0xC5, [0x18]],
            [0xE0, [0xF0, 0x09, 0x0B, 0x06, 0x04, 0x15, 0x2F, 0x54, 0x42, 0x3C, 0x17, 0x14, 0x18, 0x1B]],
            [0xE1, [0xE0, 0x09, 0x0B, 0x06, 0x04, 0x03, 0x2B, 0x43, 0x42, 0x3B, 0x16, 0x14, 0x17, 0x1B]],
            [0xF0, [0x3C]],
            [0xF0, [0x69]],
            [0x20, []],
            [0x13, []],
            [0x29, []],
        ]);
});

it('boots with the configured orientation, colour mode and inversion', function (): void {
    [, $spi] = st7796(new ST7796Configuration(
        mad_ctrl: new ST7796MADControl(false, false, false, false, true, false),
        color_mode: ST7796ColorMode::COLOR18,
        invert_display: true,
    ));

    expect($spi->commands())->toContain([0x36, [0x08]], [0x3A, [0x66]], [0x21, []]);
});

it('opens a 480-wide window with 16-bit coordinates, then writes RAM', function (): void {
    [$panel, $spi] = st7796();
    $before = count($spi->writes);

    $panel->transmit(300, 200, [0xFF, 0xFF], 1, 1);

    expect($spi->commands($before))->toBe([...st77xxWindow(300, 200, 300, 200), [0x2C, [0xFF, 0xFF]]]);
});

it('reads and writes settings through properties', function (): void {
    [$panel, $spi] = st7796();
    $before = count($spi->writes);

    $panel->sleep_mode_on = true;
    $panel->color_mode = ST7796ColorMode::COLOR18;

    expect($spi->commands($before))->toBe([[0x10, []], [0x3A, [0x66]]])
        ->and($panel->sleep_mode_on)->toBeTrue()
        ->and($panel->formatSpec()->bit_depth)->toBe(BitDepth::B18)
        ->and($panel->mad_ctrl->toByte())->toBe(0x28)
        ->and(fn () => $panel->nope = 1)->toThrow(ST77xxException::class, "Invalid property 'nope'");
});
