---
type: Guide
title: Connecting an ST77xx panel
description: Build a panel from an SPI device plus DC and RST output pins, over FTDI MPSSE or Linux spidev.
tags: [spi, transport, dc, rst, mpsse, spidev]
status: draft
generated: { by: claude-opus-5/claude-code, at: "2026-09-16T00:00:00Z" }
sources:
  - id: transport
    resource: src/Transports/ST77xxSPITransport.php
    title: ST77xxSPITransport
  - id: base
    resource: src/Transports/ST77xxDataTransport.php
    title: ST77xxDataTransport
  - id: usb-factory
    resource: microscrap/scrapyard-usb:src/SPI/MpsseSPIConnectionFactory.php
    title: MpsseSPIConnectionFactory
---

# Transport

`new ST77xxSPITransport(SPITransport $transport, DigitalOutTransport $dc, DigitalOutTransport $rst, int $max_packet_size = 2048)`[^transport]

- `command(reg, params)`: DC low → write `[reg]` → params as data. Returns write result of register byte.
- `data(array|string)`: DC high per chunk ≤ `max_packet_size`.
- `reset(us)`: RST high → low → high, `us` each.
- `close()`: DC + RST `close()`.
- `maxPacketSize(n)`: panels call at boot from config.[^base]

# FT232H (MPSSE)

```php
$spi = SPI::driver('usb')->connectTo('ft232h')->mode(0)->clockRate(MPSSEClockRate::TEN_MHZ)->register()->device('ft232h', 0);
$dc  = DigitalIO::driver('usb')->output('ft232h', 1);   // GPIOL1 / D5
$rst = DigitalIO::driver('usb')->output('ft232h', 2);   // GPIOL2 / D6
```

SPI `register()` also registers context into DigitalIO usb driver → pins from same board. CS = D3. Clock from `clockRate()`.[^usb-factory]

# spidev

```php
$spi = SPI::driver('native')->connectTo(0)->mode(0)->speed(40_000_000)->register()->device(0, 0);
$pins = DigitalIO::driver('native')->connectTo(0)->register();
$dc = $pins->output(0, 24); $rst = $pins->output(0, 25);
```

# Related

* [wiring-config](/wiring-config.md) · [traps/mpsse-clock-rate](/traps/mpsse-clock-rate.md)

[^transport]: ST77xxSPITransport
[^base]: ST77xxDataTransport
[^usb-factory]: MpsseSPIConnectionFactory
