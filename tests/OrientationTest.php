<?php

use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735MADControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\ST7735;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\ST7735Configuration;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789MADControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\ST7789;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\ST7789Configuration;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Breakouts\ST7796MADControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\ST7796;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\ST7796Configuration;

/** MADCTL by its MY / MX / MV bits, the rest clear. */
function mad7789(bool $my, bool $mx, bool $mv): ST7789MADControl
{
    return new ST7789MADControl(bottom_top_row_addresses: $my, right_left_column_addresses: $mx, pixel_direction_vertical: $mv);
}

/** @return array{0: int, 1: int, 2: int, 3: int} width, height, x offset, y offset */
function geometry(object $panel): array
{
    return [$panel->width(), $panel->height(), $panel->config()->get('x_offset'), $panel->config()->get('y_offset')];
}

/*
| A 135×240 ST7789 sits at RAM column 52 / row 40 under MX|MY. The four rotations
| Adafruit's ST7789 driver uses (MX|MY, MY|MV, none, MX|MV) put the window at
| (52, 40), (40, 52), (53, 40) and (40, 53).
*/
it('moves size and offsets with each rotation of a panel smaller than its RAM', function (bool $my, bool $mx, bool $mv, array $expected): void {
    [$transport] = st77xxWire();
    $panel = new ST7789($transport, new ST7789Configuration(width: 135, height: 240, x_offset: 52, y_offset: 40, mad_ctrl: mad7789(true, true, false)), boot_now: true);

    $panel->mad_ctrl = mad7789($my, $mx, $mv);

    expect(geometry($panel))->toBe($expected);
})->with([
    'MX|MY (as configured)' => [true, true, false, [135, 240, 52, 40]],
    'MY|MV' => [true, false, true, [240, 135, 40, 52]],
    'none' => [false, false, false, [135, 240, 53, 40]],
    'MX|MV' => [false, true, true, [240, 135, 40, 53]],
]);

it('comes back to the configured geometry after a full turn', function (): void {
    [$transport] = st77xxWire();
    $panel = new ST7789($transport, new ST7789Configuration(width: 135, height: 240, x_offset: 52, y_offset: 40, mad_ctrl: mad7789(true, true, false)), boot_now: true);

    foreach ([mad7789(true, false, true), mad7789(false, false, false), mad7789(false, true, true), mad7789(true, true, false)] as $mad) {
        $panel->mad_ctrl = $mad;
    }

    expect(geometry($panel))->toBe([135, 240, 52, 40]);
});

it('swaps size and keeps zero offsets on a panel that fills its RAM', function (string $class, string $config, object $from, object $to, array $before, array $after): void {
    [$transport] = st77xxWire();
    $panel = new $class($transport, new $config(width: $before[0], height: $before[1], mad_ctrl: $from), boot_now: true);

    expect(geometry($panel))->toBe([...$before, 0, 0]);

    $panel->mad_ctrl = $to;

    expect(geometry($panel))->toBe([...$after, 0, 0]);
})->with([
    'ST7789 240×320 portrait → landscape' => [ST7789::class, ST7789Configuration::class, mad7789(false, false, false), mad7789(false, true, true), [240, 320], [320, 240]],
    'ST7796 480×320 landscape → portrait' => [ST7796::class, ST7796Configuration::class, new ST7796MADControl, new ST7796MADControl(pixel_direction_vertical: false), [480, 320], [320, 480]],
    'ST7735 128×160 portrait → landscape' => [ST7735::class, ST7735Configuration::class, new ST7735MADControl, new ST7735MADControl(pixel_direction_vertical: true), [128, 160], [160, 128]],
]);

it('addresses the rotated window once MADCTL changes', function (): void {
    [$transport, $spi] = st77xxWire();
    $panel = new ST7789($transport, new ST7789Configuration(width: 240, height: 320), boot_now: true);

    $panel->mad_ctrl = mad7789(false, true, true);
    $before = count($spi->writes);
    $panel->transmit(0, 0, array_fill(0, 4, 0));

    expect(array_slice($spi->commands($before), 0, 2))->toBe(st77xxWindow(0, 0, 319, 239));
});
