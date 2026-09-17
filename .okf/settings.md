---
type: Reference
title: Panel settings
description: Property reads and writes on ST77xx panels, the setters behind them, and orientation through MADCTL.
tags: [settings, properties, madctl, orientation]
status: draft
generated: { by: claude-opus-5/claude-code, at: "2026-09-16T00:00:00Z" }
sources:
  - id: bootstrap
    resource: src/ST7789/Concerns/ST7789Bootstrap.php
    title: __get / __set
  - id: mad
    resource: src/ST7789/Breakouts/ST7789MADControl.php
    title: ST7789MADControl
---

# Properties

Read: any configuration key (`$panel->width`, `$panel->gamma_positive`, …).[^bootstrap]

Write (all chips): `display_on` → `setDisplay`; `sleep_mode_on` → `setSleepMode`; `invert_display` → `setDisplayInversion`; `mad_ctrl` → `setMADControl`; `color_mode` → `setPixelFormat`. Plus each chip's register keys → their setters (see [controllers](/controllers.md)). Size, offsets, packet size → `invalidProperty`.

State lives in configuration; driver never reads controller.

# MADCTL

`{Chip}MADControl(bottom_top_row_addresses, right_left_column_addresses, pixel_direction_vertical, bottom_top_refresh, bgr_order_mode, right_left_refresh)` → bits 7..2.[^mad] MV (`pixel_direction_vertical`) swaps axes; MY/MX mirror; bit 3 BGR.

# Related

* [traps/madctl-swaps-size](/traps/madctl-swaps-size.md) · [drawing](/drawing.md)

[^bootstrap]: __get / __set
[^mad]: ST7789MADControl
