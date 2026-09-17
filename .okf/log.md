# dept-of-scrapyard-robotics/st77xx Update Log

## 2026-09-16
* **Update**: [drawing](/drawing.md), [overview](/overview.md) — `fillRgb565()` replaced by mode-neutral `fill(r, g, b)` packed from the current FormatSpec; colour mode switching documented.
* **Removal**: traps/fill-is-rgb565 (fill no longer depth-bound); unused `ST77xxCatalogIc`, `ST77xxConsoleCommand`, `ST77xxSmokeColor` enums.
* **Creation**: bundle seeded for 0.8.0 — [overview](/overview.md), [connecting](/connecting.md), [controllers](/controllers.md), [drawing](/drawing.md), [settings](/settings.md), [wiring-config](/wiring-config.md), four [traps](/traps/index.md).
