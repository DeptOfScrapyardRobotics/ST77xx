<?php

use DeptOfScrapyardRobotics\Displays\ST77xx\Tests\Support\FakeOutputPin;
use DeptOfScrapyardRobotics\Displays\ST77xx\Tests\Support\FakeSPITransport;
use DeptOfScrapyardRobotics\Displays\ST77xx\Transports\ST77xxSPITransport;

/** @return array{0: ST77xxSPITransport, 1: FakeSPITransport, 2: FakeOutputPin, 3: FakeOutputPin} */
function st77xxWire(int $max_packet_size = 2048): array
{
    $dc = new FakeOutputPin(1);
    $rst = new FakeOutputPin(2);
    $spi = new FakeSPITransport($dc);

    return [new ST77xxSPITransport($spi, $dc, $rst, $max_packet_size), $spi, $dc, $rst];
}

/** Column and row window commands for a rectangle, 16-bit big-endian. */
function st77xxWindow(int $x0, int $y0, int $x1, int $y1): array
{
    return [
        [0x2A, [$x0 >> 8, $x0 & 0xFF, $x1 >> 8, $x1 & 0xFF]],
        [0x2B, [$y0 >> 8, $y0 & 0xFF, $y1 >> 8, $y1 & 0xFF]],
    ];
}
