<?php

use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\ST7735;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\ST7789;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\ST7796;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\ST7796Configuration;
use DeptOfScrapyardRobotics\Displays\ST77xx\Tests\Support\FakeMemorySPITransport;
use DeptOfScrapyardRobotics\Displays\ST77xx\Tests\Support\FakeOutputPin;
use DeptOfScrapyardRobotics\Displays\ST77xx\Transports\ST77xxSPITransport;
use GeneralPurposeIO\Contracts\IntegratedCircuits\PipeablePanel;

/** @return array{0: ST7796, 1: FakeMemorySPITransport} a booted ST7796 on a bus that writes from memory, boot writes cleared */
function st7796Piped(): array
{
    $dc = new FakeOutputPin(1);
    $spi = new FakeMemorySPITransport($dc);
    $panel = new ST7796(new ST77xxSPITransport($spi, $dc, new FakeOutputPin(2)), new ST7796Configuration, boot_now: true);
    $spi->writes = [];

    return [$panel, $spi];
}

it('makes every ST77xx a pipeable panel', function (string $chip) {
    expect(is_subclass_of($chip, PipeablePanel::class))->toBeTrue();
})->with([ST7735::class, ST7789::class, ST7796::class]);

it('opens a window with CASET, RASET and RAMWR under DC low, then raises DC for the pixels', function () {
    [$panel, $spi] = st7796Piped();
    $x = $panel->config()->get('x_offset');
    $y = $panel->config()->get('y_offset');

    $panel->openWindow(10, 20, 30, 2);
    $panel->pixelBus()->writeFrom([[0x1000, 60], [0x1000 + 960, 60]]);

    expect($spi->commands())->toBe([...st77xxWindow($x + 10, $y + 20, $x + 39, $y + 21), [0x2C, []]])
        ->and($spi->spans)->toBe([['data', [[0x1000, 60], [0x1000 + 960, 60]]]]);
});

it('answers no pixel bus when its SPI bus cannot write from memory', function () {
    [$transport] = st77xxWire();
    $panel = new ST7796($transport, new ST7796Configuration);

    expect($panel->pixelBus())->toBeNull();
});
