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

    expect($dc->closed)->toBeTrue()->and($rst->closed)->toBeTrue();
});
