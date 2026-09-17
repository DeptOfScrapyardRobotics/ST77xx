---
type: Configuration
title: Wiring config
description: circuits.st7735, circuits.st7789 and circuits.st7796 config keys, provider merge, and the st77xx-config publish tag.
resource: config/st7789.php
tags: [config, circuits, publish, provider]
status: draft
generated: { by: claude-opus-5/claude-code, at: "2026-09-16T00:00:00Z" }
sources:
  - id: provider
    resource: src/Providers/ST77xxServiceProvider.php
    title: ST77xxServiceProvider
  - id: config
    resource: config/st7789.php
    title: st7789 config
---

# Merge + publish

`register()`: `config/{chip}.php` → `circuits.{chip}` for st7735, st7789, st7796; app values win. `boot()`: tag `st77xx-config` → `config/circuits/{chip}.php`.[^provider]

# Schema

Same for all three.[^config]

| Key | Default |
|---|---|
| `default_config` | `'spi'` |
| `configs.spi.driver` / `device` | `'none'` / `''` |
| `configs.spi.chip_select` | 0 |
| `configs.spi.dc` / `rst` | `{driver, device, pin}`, pins 0 / 1 |

Package reads none of it. Separate from `{Chip}Configuration`.

# Related

* [connecting](/connecting.md)

[^provider]: ST77xxServiceProvider
[^config]: st7789 config
