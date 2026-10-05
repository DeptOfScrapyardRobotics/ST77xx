<?php

use DeptOfScrapyardRobotics\Displays\ST77xx\Transports\ST77xxDataTransport;
use GeneralPurposeIO\Contracts\IntegratedCircuits\DataCommander;

it('is a data commander', function (): void {
    [$transport] = st77xxWire();

    expect($transport)->toBeInstanceOf(ST77xxDataTransport::class)
        ->and($transport)->toBeInstanceOf(DataCommander::class);
});

it('sends the register with DC low, then its parameters with DC high', function (): void {
    [$transport, $spi] = st77xxWire();

    $transport->command(0x2A, [0x00, 0x00, 0x00, 0xEF]);
    $transport->command(0x29);

    expect($spi->writes)->toBe([
        ['cmd', [0x2A]],
        ['data', [0x00, 0x00, 0x00, 0xEF]],
        ['cmd', [0x29]],
    ]);
});

it('streams data in packets of the configured size, arrays and strings alike', function (): void {
    [$transport, $spi] = st77xxWire(max_packet_size: 4);

    $transport->data([1, 2, 3, 4, 5, 6]);
    $transport->data("\x07\x08\x09\x0A\x0B");

    expect($spi->writes)->toBe([
        ['data', [1, 2, 3, 4]], ['data', [5, 6]],
        ['data', [7, 8, 9, 10]], ['data', [11]],
    ]);
});

it('changes the packet size in place', function (): void {
    [$transport, $spi] = st77xxWire(max_packet_size: 4);

    expect($transport->maxPacketSize(2))->toBe($transport);

    $transport->data([1, 2, 3]);

    expect($spi->writes)->toBe([['data', [1, 2]], ['data', [3]]]);
});

it('pulses RST high, low, high', function (): void {
    [$transport, , , $rst] = st77xxWire();

    $transport->reset(1);

    expect($rst->levels)->toBe([true, false, true]);
});

it('releases DC and RST on close', function (): void {
    [$transport, , $dc, $rst] = st77xxWire();

    $transport->close();

    expect($dc->closed())->toBeTrue()->and($rst->closed())->toBeTrue();
});

it('throws when the bus refuses a command or data write', function (): void {
    [$transport, $spi] = st77xxWire();
    $spi->answer = -1;

    expect(fn () => $transport->command(0x2A, [0, 0, 0, 0xEF]))->toThrow(\DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException::class, 'ST77xx SPI command 0x2A write failed: -1 of 1 bytes')
        ->and(fn () => $transport->data("\x01\x02\x03"))->toThrow(\DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException::class, 'ST77xx SPI data write failed: -1 of 3 bytes');
});

it('throws when the bus writes fewer bytes than asked', function (): void {
    [$transport, $spi] = st77xxWire();
    $spi->answer = 2;

    expect(fn () => $transport->data([1, 2, 3]))->toThrow(\DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException::class, 'write failed: 2 of 3 bytes');
});

it('refuses a packet size below 1', function (): void {
    [$transport] = st77xxWire();

    expect(fn () => $transport->maxPacketSize(0))->toThrow(\DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException::class, 'max_packet_size 0 must be at least 1');
});

it('refuses a gamma or output-adjust byte out of range instead of masking it', function (callable $build, string $message): void {
    expect($build)->toThrow(\DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException::class, $message);
})->with([
    'ST7789 gamma' => [fn () => new \DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789GammaPositive(v1: 0x100), 'Valid v1 values are between 0 and 255, you input 256.'],
    'ST7789 negative gamma' => [fn () => new \DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789GammaNegative(v62: -1), 'Valid v62 values are between 0 and 255'],
    'ST7796 gamma' => [fn () => \DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Breakouts\ST7796GammaPositive::fromBytes(array_fill(0, 14, 0x1FF)), 'between 0 and 255, you input 511'],
    'ST7796 output adjust' => [fn () => new \DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Breakouts\ST7796DisplayOutputCtrlAdjust(adjustment_3: 300), 'Valid adjustment_3 values'],
    'ST7735 gamma (6-bit)' => [fn () => new \DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735GammaPositive(pk0: 0x40), 'Valid pk0 values are between 0 and 63, you input 64.'],
]);
