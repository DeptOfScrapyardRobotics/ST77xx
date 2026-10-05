# Agent guidelines — dept-of-scrapyard-robotics/st77xx

## Knowledge Bundle (OKF)

This package ships an Open Knowledge Format bundle at [`.okf/`](.okf/) (excluded from the Composer dist via `.gitattributes` `export-ignore`). Before changing code or advising on this package: read [`.okf/index.md`](.okf/index.md) first, open only the concepts the task needs, prefer `status: stable` over `draft`. When you learn something durable, update the affected concept(s), bump `generated.at`, and append [`.okf/log.md`](.okf/log.md); new or changed concepts stay `status: draft` until a human verifies them. The bundle documents the package, never a session.

Do **not** create `.okf` folders under `src/*` — knowledge for this package lives at the package root only. Catalog, transport and adapter semantics belong to `scrapyard-io/framework`'s bundle, framebuffer packing to Surface's; point there, do not restate them here.

## Where this package sits

`ext-posi` / `ext-ftdi` → `microscrap/*` → `scrapyard-io/framework` (protocol managers, transports, the circuit catalog, `DisplayPanel`) → **`dept-of-scrapyard-robotics/st77xx`** (panel drivers) → apps and Surface.

## Package rules (quick) — 0.10.x

- Composer: `dept-of-scrapyard-robotics/st77xx` **0.10.0**. PHP `^8.4|^8.5|^8.6`. Namespace `DeptOfScrapyardRobotics\Displays\ST77xx\` → `src/`.
- **Requires split components only**: `gpio/contracts`, `gpio/integrated-circuits`, `gpio/nuts-and-bolts`, `venusian-surface/contracts`, `venusian-voyager/nuts-and-bolts`, `venusian-voyager/vessel`. Never `scrapyard-io/framework`, `venusian/surface` or `venusian/framework`. Protocol components and adapters are `suggest`; requires follow imports (and helper functions such as `byte2bits()`).
- **Three controllers, one shape.** `ST7735/`, `ST7789/`, `ST7796/` each have the panel, `{Chip}Configuration`, `Concerns\{Chip}API` (setters: chip, then configuration), `Concerns\{Chip}Bootstrap` (`__get` any key, `__set` keys with setters, `_boot()`), `Breakouts\*`, `Enums\*`. A change to a shared behaviour lands in all three, tests included.
- **Panel = `Bootable` + `DisplayPanel` + `WindowAddressable` + `Switchable`.** Not `RefreshesOnCommand`: TFTs show on write. `formatSpec()` is `ROW_MAJOR` / colour-mode depth / `Endianness::MSB`, rebuilt by `setPixelFormat()`. Surface 0.10 has no `FormatSpecification` interface; keep the three `formatSpec` methods as plain methods.
- **The factory is the config shape.** `ConjuresOverSPI::spi()` parameters are exactly a `circuits.<chip>.configs.*` entry's keys; the provider catalogs `st7735`, `st7789`, `st7796`. A new config key = a new factory parameter, and the reverse. Bus first, DC and RST after. `speed` always reaches the chip select through the transport's `speed()`; a shared bus in another mode is refused.
- **Orientation moves geometry.** `setMADControl()` calls `ST77xxOrientation::reorient()` before storing: width, height and offsets follow MX / MY / MV against `ramGeometry()` (ST7735 132×162, ST7789 240×320, ST7796 320×480). Constructor arguments describe one orientation together and are never reoriented.
- **Every write is checked.** `ST77xxSPITransport` compares each write's result with the bytes sent and throws `spiWriteFailed`; never return or swallow `-1`.
- **Register breakouts** are `readonly` classes (`DataRegister`s with `toBits` / `fromByte` / `none`, or multi-byte ones with `toBytes` / `fromBytes`). Integer fields are range-checked in the constructor through `invalidRegisterValue`, never masked; enum and bool fields carry their own range.
- **Reach the framework through the container.** 0.10 has no protocol aliases or facades. The factory resolves `gpio.spi` / `gpio.digital` from `ControlPanel::getInstance()`; apps call `app('circuit')->conjure()`.
- **Config** merges under `circuits.st7735` / `st7789` / `st7796`; publish tag `st77xx-config` → `config/circuits/*.php`. Package defaults are `driver => 'none'`.
- **Exceptions** descend from `GeneralPurposeIO\Contracts\IntegratedCircuits\CircuitException` → `GPIOLevelException`.
- Enums int-backed, FULLY UPPERCASE cases. No class constants. `is_null($x)` over `$x === null`.

## Verification

```bash
vendor/bin/pest            # recording fake buses and pins; no hardware
```

Run it under NTS and ZTS PHP before every commit. Suites stay hardware-free: panels are for scratch smoke scripts, never committed and never in `tests/`. `.okf/runbooks/hardware-smoke.md` has the script's shape.

Hardware truth: a 480×320 ST7796 on a Raspberry Pi 5's spidev0.0 with DC on GPIO22 and RST on GPIO24, and a 240×320 ST7789 on an FT232H over SPI with chip select on GPIO0 (D4), DC on GPIO1 (D5), RST on GPIO2 (D6). A frame is proven only when someone watched the panel show it.
