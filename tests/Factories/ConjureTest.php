<?php

use DeptOfScrapyardRobotics\Displays\ST77xx\Providers\ST77xxServiceProvider;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\ST7789;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\ST7796;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;
use GeneralPurposeIO\Contracts\SPI\SPIMode;

/** The provider's config with the app's wiring over it, booted onto the bench's catalog. */
function st77xxBench(string $chip, array $wiring): array
{
    $bench = fakeBench(['circuits' => [$chip => $wiring]]);
    $provider = new ST77xxServiceProvider($bench['app']);
    $provider->register();
    $provider->boot();

    return $bench;
}

function st77xxSpiWiring(array $overrides = []): array
{
    return array_replace_recursive([
        'default_config' => 'spi',
        'configs' => ['spi' => [
            'driver' => 'fake',
            'device' => 'ft232h',
            'chip_select' => 0,
            'width' => 240,
            'height' => 320,
            'dc' => ['driver' => 'fake', 'device' => 'ft232h', 'pin' => 1],
            'rst' => ['driver' => 'fake', 'device' => 'ft232h', 'pin' => 2],
        ]],
    ], $overrides);
}

it('conjures a booted panel: bus in mode 0 at 10 MHz, then DC and RST on the same device', function (): void {
    $bench = st77xxBench('st7789', st77xxSpiWiring());

    $panel = $bench['app']->make('circuit')->conjure('st7789');
    $slave = $bench['spi']->slaves['ft232h:0'];

    expect($panel)->toBeInstanceOf(ST7789::class)
        ->and($panel->hasBooted())->toBeTrue()
        ->and([$panel->width(), $panel->height()])->toBe([240, 320])
        ->and($bench['spi']->settingsOf('ft232h')->mode)->toBe(SPIMode::MODE_0)
        ->and($slave->clock())->toBe(10_000_000)
        ->and($bench['spi']->opened)->toBe(['ft232h'])
        ->and($bench['digital']->opened)->toBe(['ft232h'])
        ->and($bench['digital']->outputs['ft232h:2']->levels)->toBe([true, false, true])
        ->and($slave->writes[0])->toBe(['raw', [0x28]]);
});

it('clocks its chip select at the configured speed and opens the configured mode', function (): void {
    $bench = st77xxBench('st7789', st77xxSpiWiring(['configs' => ['spi' => ['speed' => 32_000_000, 'mode' => 3]]]));

    $bench['app']->make('circuit')->conjure('st7789');

    expect($bench['spi']->slaves['ft232h:0']->clock())->toBe(32_000_000)
        ->and($bench['spi']->settingsOf('ft232h')->mode)->toBe(SPIMode::MODE_3);
});

it('passes offsets, inversion and MADCTL flags through to the configuration', function (): void {
    $bench = st77xxBench('st7789', st77xxSpiWiring(['configs' => ['spi' => [
        'width' => 135, 'height' => 240, 'x_offset' => 52, 'y_offset' => 40, 'invert_display' => false,
        'mad_ctrl' => ['bottom_top_row_addresses' => true, 'right_left_column_addresses' => true],
    ]]]));

    $panel = $bench['app']->make('circuit')->conjure('st7789');

    expect($panel->config()->get('x_offset'))->toBe(52)
        ->and($panel->config()->get('y_offset'))->toBe(40)
        ->and($panel->config()->get('invert_display'))->toBeFalse()
        ->and($panel->config()->get('mad_ctrl')->toByte())->toBe(0xC0);
});

it('keeps the controller defaults for keys left null', function (): void {
    $bench = st77xxBench('st7796', st77xxSpiWiring(['configs' => ['spi' => ['width' => null, 'height' => null]]]));

    $panel = $bench['app']->make('circuit')->conjure('st7796');

    expect($panel)->toBeInstanceOf(ST7796::class)
        ->and([$panel->width(), $panel->height()])->toBe([480, 320]);
});

it('shares a bus the app opened in the same mode and refuses one in another', function (int $bus_mode, bool $shared): void {
    $bench = st77xxBench('st7789', st77xxSpiWiring());
    $bench['app']->make('gpio.spi')->driver('fake')->connectTo('ft232h')->mode($bus_mode)->register();

    $conjure = fn () => $bench['app']->make('circuit')->conjure('st7789');

    $shared
        ? expect($conjure())->toBeInstanceOf(ST7789::class)
        : expect($conjure)->toThrow(ST77xxException::class, "runs in mode {$bus_mode}; this panel is configured for mode 0");
})->with([
    'mode 0' => [0, true],
    'mode 3' => [3, false],
]);

it('refuses a mode that does not exist and a clock below 1 Hz before touching the bus', function (array $spi, string $message): void {
    $bench = st77xxBench('st7789', st77xxSpiWiring(['configs' => ['spi' => $spi]]));

    expect(fn () => $bench['app']->make('circuit')->conjure('st7789'))->toThrow(ST77xxException::class, $message)
        ->and($bench['spi']->opened)->toBe([]);
})->with([
    'mode 4' => [['mode' => 4], 'SPI mode 4 does not exist'],
    'clock 0' => [['speed' => 0], 'SPI clock 0 Hz'],
]);

it('names a DC or RST pin config missing its driver, device or pin', function (string $line): void {
    $wiring = st77xxSpiWiring();
    unset($wiring['configs']['spi'][$line]['device']);
    $bench = st77xxBench('st7789', $wiring);

    expect(fn () => $bench['app']->make('circuit')->conjure('st7789'))->toThrow(ST77xxException::class, "needs its {$line} pin");
})->with(['dc', 'rst']);

it('builds without booting when boot_now is false', function (): void {
    $bench = st77xxBench('st7789', st77xxSpiWiring(['configs' => ['spi' => ['boot_now' => false]]]));

    $panel = $bench['app']->make('circuit')->conjure('st7789');

    expect($panel->hasBooted())->toBeFalse()
        ->and($bench['spi']->slaves['ft232h:0']->writes)->toBe([]);
});
