---
type: Package
title: dept-of-scrapyard-robotics/st77xx
description: ST7735, ST7789 and ST7796 TFT drivers for scrapyard-io/framework 0.8 — identity, requires, shared class shape, errors.
resource: composer.json
tags: [st7735, st7789, st7796, tft, display, spi, package]
status: draft
generated: { by: claude-opus-5/claude-code, at: "2026-09-16T00:00:00Z" }
sources:
  - id: composer
    resource: composer.json
    title: Package manifest
  - id: st7789
    resource: src/ST7789/ST7789.php
    title: ST7789
  - id: exception
    resource: src/ST77xxException.php
    title: ST77xxException
  - id: fill
    resource: src/ST77xxFills.php
    title: ST77xxFills
---

# Identity

| Field | Value |
|---|---|
| Composer | `dept-of-scrapyard-robotics/st77xx` **0.8.0** |
| PHP | `^8.4\|^8.5\|^8.6` |
| Namespace | `DeptOfScrapyardRobotics\Displays\ST77xx\` → `src/` |
| Provider | `Providers\ST77xxServiceProvider` (`extra.venusian.providers`) |

# Requires

Split components only.[^composer]

| Package | Why |
|---|---|
| `gpio/contracts` | SPI + digital-out transport, `DisplayPanel`, `DataCommander`; exception root |
| `gpio/integrated-circuits` | `Bootable`, `DataRegister` |
| `gpio/nuts-and-bolts` | `byte2bits()` in breakouts |
| `surface/contracts` | `FormatSpec`, `FormatSpecification`, framebuffer enums |
| `venusian-voyager/nuts-and-bolts` | `ServiceProvider` |

Suggests: `gpio/spi`, `gpio/digital`, `microscrap/scrapyard-usb` (ext-ftdi), `microscrap/scrapyard-linux` (ext-posi).

# Shape (same for each chip)

| Piece | ST7735 / ST7789 / ST7796 |
|---|---|
| panel | `ST77xx\{Chip}\{Chip}` — `Bootable` + `DisplayPanel` + `FormatSpecification`, uses `ST77xxFills`[^st7789] |
| settings | `{Chip}Configuration` — `get()` / `set()`, unknown key → `invalidProperty` |
| setters | `Concerns\{Chip}API` — write chip, then configuration |
| lifecycle | `Concerns\{Chip}Bootstrap` — `__get` (any config key), `__set` (keys with setters), `_boot()` |
| registers | `Breakouts\*`, `Enums\*` (incl. `{Chip}OpCode`, `{Chip}ColorMode`) |

Shared: `Transports\ST77xxSPITransport`, `ST77xxFills`, `ST77xxException`, `Providers\ST77xxServiceProvider`.

Ctor: `new {Chip}(ST77xxDataTransport $transport, {Chip}Configuration $props, bool $boot_now = false)`; FormatSpec built in ctor. `close()` → transport releases DC + RST.

Colour mode: 12 / 16 / 18-bit, switchable any time; FormatSpec + `fill()` follow.[^fill]

# Errors

`ST77xxException` → `CircuitException` → `GPIOLevelException`.[^exception]

| Factory | When |
|---|---|
| `invalidRegisterValue` | breakout field or fill channel out of range |
| `invalidColorMode` | `setPixelFormat()` int not 12 / 16 / 18 |
| `invalidProperty` | unknown key, or write to key without setter |

# Live reference

240×320 ST7789, FT232H, MPSSE SPI 10 MHz, DC GPIOL1, RST GPIOL2, landscape 320×240: boot 208 ms, fill ~270 ms, full frame 279 ms, 100×60 window 32 ms, confirmed on the panel.

# Related

* [controllers](/controllers.md) · [connecting](/connecting.md) · [drawing](/drawing.md)

[^composer]: Package manifest
[^st7789]: ST7789
[^exception]: ST77xxException
[^fill]: ST77xxFills
