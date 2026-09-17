# Agent guidelines — dept-of-scrapyard-robotics/st77xx

## Knowledge Bundle (OKF)

This package ships an Open Knowledge Format bundle at [`.okf/`](.okf/) (excluded from the Composer dist via `.gitattributes` `export-ignore`). Before changing code or advising on this package: read [`.okf/index.md`](.okf/index.md) first, open only the concepts the task needs, prefer `status: stable` over `draft`. When you learn something durable, update the affected concept(s) and append [`.okf/log.md`](.okf/log.md); new or changed concepts stay `status: draft` until a human verifies them.

Do **not** create `.okf` folders under `src/*` — knowledge for this package lives at the package root only. Transport, dock and framebuffer semantics belong to `scrapyard-io/framework` and `venusian/surface`; point there, do not restate them here.

## Where this package sits

`ext-posi` / `ext-ftdi` → `microscrap/*` → `scrapyard-io/framework` (protocol managers, transports) → **`dept-of-scrapyard-robotics/st77xx`** (ST7735 / ST7789 / ST7796 panel drivers) → Surface CPU engines, which pack frames against each panel's `FormatSpec`.

## Package rules (quick) — 0.8.x

- Composer: `dept-of-scrapyard-robotics/st77xx` **0.8.0**. PHP `^8.4|^8.5|^8.6`. One namespace, `DeptOfScrapyardRobotics\Displays\ST77xx\` → `src/`.
- **Requires split components only**: `gpio/contracts`, `gpio/integrated-circuits`, `gpio/nuts-and-bolts`, `surface/contracts`, `venusian-voyager/nuts-and-bolts`. Never `scrapyard-io/framework`, `venusian/framework` or `venusian/surface`. Protocol components and adapters are `suggest`.
- **Three chips, one shape.** Each of `ST7735/`, `ST7789/`, `ST7796/` has a panel class (`Bootable` + `DisplayPanel` + `FormatSpecification`), a `*Configuration`, `Concerns/*API` (setters) and `Concerns/*Bootstrap` (`__get`/`__set`, `_boot`). A fix to one chip's shape is a fix to all three.
- **The configuration is the state.** Every setter writes the chip, then the configuration. `__get` reads any configuration key; `__set` accepts only keys with a setter. Nothing reads `$this->` settings or writes them directly.
- **The panel owns no pixels.** `formatSpec()` is row-major, big-endian, at the colour mode's depth, rebuilt by `setPixelFormat()`. `transmit()` takes bytes already packed; `fill(r, g, b)` is the only drawing the package does, and it packs from the current `formatSpec()`.
- **No colour depth is favoured.** 12-, 16- and 18-bit modes switch at any time through `setPixelFormat()` / `color_mode`; anything that produces pixel bytes must read the current `FormatSpec`, never assume RGB565.
- **Boot sequences are pinned** in tests to the datasheet / Adafruit init bytes. Changing a breakout default changes those bytes; update the test only with a reason.
- **Transport**: `ST77xxSPITransport` — register with DC low, parameters and data with DC high, chunked by `max_packet_size`; `reset()` pulses RST; `close()` releases DC and RST only.
- **Register breakouts** are `readonly` `DataRegister`s from `gpio/integrated-circuits`; register-range errors are `ST77xxException::invalidRegisterValue()`.
- **Reach the framework through MagicAliases** (`SPI::`, `DigitalIO::`), never `app('gpio.*')`.
- **Config** merges under `circuits.st7735` / `circuits.st7789` / `circuits.st7796`; publish tag `st77xx-config` → `config/circuits/*.php`. The package reads none of it.
- **Exceptions** descend from `GeneralPurposeIO\Contracts\IntegratedCircuits\CircuitException` → `GPIOLevelException`.
- Enums int- or string-backed, FULLY UPPERCASE cases. No class constants. `is_null($x)` over `$x === null`.

## Verification

```bash
vendor/bin/pest            # recording fakes; no hardware
```

Hardware truth is a 240×320 ST7789 on an FT232H (MPSSE SPI, DC on GPIOL1, RST on GPIOL2). Announce with `say` before any run that lights a panel.
