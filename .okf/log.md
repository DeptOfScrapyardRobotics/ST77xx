# dept-of-scrapyard-robotics/st77xx Update Log

## 2026-10-04
* **Update**: [drawing](/drawing.md) piping: ST7735 / ST7789 / ST7796 implement `PipeablePanel` (`openWindow()`, `pixelBus()`) through `ST77xxPipes`; `ST77xxDataTransport` gains `beginData()` and `memoryBus()`. Pinned by `tests/PipeTest.php`.
* **Update**: 0.10 port. [overview](/overview.md): requires 0.10 split components plus `venusian-surface/contracts` and `venusian-voyager/vessel`; no `FormatSpecification` in Surface 0.10; every SPI write checked; new exceptions.
* **Update**: [connecting](/connecting.md) rewritten for `conjure()` and `spi()`: container managers, 10 MHz default clock per chip select, configured mode refused on a bus in another, offsets and inversion from config, both benches. [wiring-config](/wiring-config.md): `speed`, `x_offset`, `y_offset`, `invert_display`, `protocol`, `boot_now`.
* **Update**: [settings](/settings.md): a `mad_ctrl` write moves width, height and offsets to the new orientation. [drawing](/drawing.md): packing through a Surface framebuffer; live reference from both benches. [controllers](/controllers.md): orientation note.
* **Update**: [overview](/overview.md) errors: gamma tables and ST7796 output adjust refuse out-of-range bytes (ST7735 gamma 0–63, others 0–255) instead of masking them.
* **Removal**: the three 0.8 warning notes (MPSSE clock, MADCTL size, unchecked writes): the 0.10 FT232H adapter honours `speed()` and the factory sets it, MADCTL writes carry size and offsets, writes throw on failure.
* **Creation**: [hardware smoke](/runbooks/hardware-smoke.md) runbook.

## 2026-09-18
* **Update**: [overview](/overview.md) — `ST7735` / `ST7789` / `ST7796` also implement `WindowAddressable` and `Switchable`, the new `DisplayPanel` children in gpio/contracts; `DisplayPanel` itself now carries `transmit()` and extends `FormatSpecification`. Pinned by `tests/ContractsTest.php`; suite 59 passed.

## 2026-09-16
* **Update**: [drawing](/drawing.md), [overview](/overview.md) — `fillRgb565()` replaced by mode-neutral `fill(r, g, b)` packed from the current FormatSpec; colour mode switching documented.
* **Removal**: traps/fill-is-rgb565 (fill no longer depth-bound); unused `ST77xxCatalogIc`, `ST77xxConsoleCommand`, `ST77xxSmokeColor` enums.
* **Creation**: bundle seeded for 0.8.0 — [overview](/overview.md), [connecting](/connecting.md), [controllers](/controllers.md), [drawing](/drawing.md), [settings](/settings.md), [wiring-config](/wiring-config.md), four [traps](/traps/index.md).
