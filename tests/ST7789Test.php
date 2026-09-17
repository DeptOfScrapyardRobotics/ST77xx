<?php

use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789MADControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789VCOMControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Enums\ST7789ColorMode;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Enums\ST7789VCOMVoltage;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\ST7789;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\ST7789Configuration;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;
use DeptOfScrapyardRobotics\Displays\ST77xx\Tests\Support\FakeSPITransport;
use GeneralPurposeIO\Contracts\IntegratedCircuits\CircuitException;
use GeneralPurposeIO\Contracts\IntegratedCircuits\DisplayPanel;
use Surface\Contracts\Framebuffers\BitDepth;
use Surface\Contracts\Framebuffers\Endianness;
use Surface\Contracts\Framebuffers\FormatSpecification;
use Surface\Contracts\Framebuffers\PixelFormat;
use Surface\Contracts\Framebuffers\ScanDirection;

/** @return array{0: ST7789, 1: FakeSPITransport, 2: \DeptOfScrapyardRobotics\Displays\ST77xx\Tests\Support\FakeOutputPin} */
function st7789(?ST7789Configuration $config = null, bool $boot = true): array
{
    [$transport, $spi, , $rst] = st77xxWire();

    return [new ST7789($transport, $config ?? new ST7789Configuration, boot_now: $boot), $spi, $rst];
}

it('is a display panel with a format spec, sized by its configuration', function (): void {
    [$panel, $spi] = st7789(new ST7789Configuration(width: 320, height: 240), boot: false);

    expect($panel)->toBeInstanceOf(DisplayPanel::class)
        ->and($panel)->toBeInstanceOf(FormatSpecification::class)
        ->and($panel->hasBooted())->toBeFalse()
        ->and([$panel->width(), $panel->height()])->toBe([320, 240])
        ->and($spi->writes)->toBe([]);
});

it('boots with the datasheet init sequence', function (): void {
    [$panel, $spi, $rst] = st7789();

    expect($panel->hasBooted())->toBeTrue()
        ->and($rst->levels)->toBe([true, false, true])
        ->and($spi->commands())->toBe([
            [0x28, []],
            [0x11, []],
            [0x36, [0x00]],
            [0x3A, [0x55]],
            [0xB2, [0x0C, 0x0C, 0x00, 0x33, 0x33]],
            [0xB7, [0x35]],
            [0xBB, [0x19]],
            [0xC0, [0x2C]],
            [0xC2, [0x01, 0xFF]],
            [0xC3, [0x12]],
            [0xC4, [0x20]],
            [0xC6, [0x0F]],
            [0xD0, [0xA4, 0xA1]],
            [0xE0, [0xD0, 0x04, 0x0D, 0x11, 0x13, 0x2B, 0x3F, 0x54, 0x4C, 0x18, 0x0D, 0x0B, 0x1F, 0x23]],
            [0xE1, [0xD0, 0x04, 0x0C, 0x11, 0x13, 0x2C, 0x3F, 0x44, 0x51, 0x2F, 0x1F, 0x1F, 0x20, 0x23]],
            [0x21, []],
            [0x13, []],
            [0x29, []],
        ]);
});

it('boots with the configured orientation, colour mode and panel registers', function (): void {
    [, $spi] = st7789(new ST7789Configuration(
        mad_ctrl: new ST7789MADControl(pixel_direction_vertical: true),
        color_mode: ST7789ColorMode::COLOR18,
        v_com_ctrl: new ST7789VCOMControl(ST7789VCOMVoltage::V0_875),
        invert_display: false,
    ));

    expect($spi->commands())->toContain([0x36, [0x20]], [0x3A, [0x66]], [0xBB, [0x1F]], [0x20, []])
        ->and($spi->commands())->not->toContain([0x21, []]);
});

it('describes its bytes as big-endian row-major pixels at the colour mode depth', function (): void {
    [$panel] = st7789(boot: false);

    $spec = $panel->formatSpec();

    expect($spec->pixel_format)->toBe(PixelFormat::ROW_MAJOR)
        ->and($spec->bit_depth)->toBe(BitDepth::B16)
        ->and($spec->scan_direction)->toBe(ScanDirection::TOP_TO_BOTTOM)
        ->and($spec->endianness)->toBe(Endianness::MSB);
});

it('rebuilds the format spec when the colour mode changes', function (): void {
    [$panel, $spi] = st7789();
    $before = count($spi->writes);

    $panel->color_mode = ST7789ColorMode::COLOR18;
    $panel->setPixelFormat(12);

    expect($spi->commands($before))->toBe([[0x3A, [0x66]], [0x3A, [0x53]]])
        ->and($panel->color_mode)->toBe(ST7789ColorMode::COLOR12)
        ->and($panel->formatSpec()->bit_depth)->toBe(BitDepth::B12)
        ->and(fn () => $panel->setPixelFormat(24))->toThrow(ST77xxException::class, 'Invalid color mode: 24');
});

it('opens a window with the panel offsets applied, then writes RAM', function (): void {
    [$panel, $spi] = st7789(new ST7789Configuration(width: 240, height: 240, x_offset: 0, y_offset: 80));
    $before = count($spi->writes);

    $panel->transmit(10, 20, [0xF8, 0x00, 0xF8, 0x00], 2, 1);

    expect($spi->commands($before))->toBe([
        ...st77xxWindow(10, 100, 11, 100),
        [0x2C, [0xF8, 0x00, 0xF8, 0x00]],
    ]);
});

it('reads and writes settings through properties, keeping the configuration current', function (): void {
    [$panel, $spi] = st7789();
    $before = count($spi->writes);

    $panel->display_on = false;
    $panel->sleep_mode_on = true;
    $panel->invert_display = false;
    $panel->mad_ctrl = new ST7789MADControl(right_left_column_addresses: true);

    expect($spi->commands($before))->toBe([[0x28, []], [0x10, []], [0x20, []], [0x36, [0x40]]])
        ->and($panel->display_on)->toBeFalse()
        ->and($panel->sleep_mode_on)->toBeTrue()
        ->and($panel->invert_display)->toBeFalse()
        ->and($panel->mad_ctrl->right_left_column_addresses)->toBeTrue()
        ->and($panel->config()->get('mad_ctrl'))->toBe($panel->mad_ctrl)
        ->and($panel->width)->toBe(240)
        ->and(fn () => $panel->nope)->toThrow(ST77xxException::class, "Invalid property 'nope'")
        ->and(fn () => $panel->width = 1)->toThrow(ST77xxException::class, "Invalid property 'width'");
});

it('the configuration names an unknown key', function (): void {
    $config = new ST7789Configuration;

    expect(fn () => $config->get('nope'))->toThrow(ST77xxException::class, "'nope'")
        ->and(fn () => $config->set('nope', 1))->toThrow(ST77xxException::class, "'nope'");
});

it('roots its exception at the framework circuit exception', function (): void {
    expect(ST77xxException::invalidProperty('x', 'y'))->toBeInstanceOf(CircuitException::class);
});

it('releases DC and RST on close', function (): void {
    [$transport, , $dc, $rst] = st77xxWire();
    $panel = new ST7789($transport, new ST7789Configuration);

    $panel->close();

    expect($dc->closed)->toBeTrue()->and($rst->closed)->toBeTrue();
});
