---
type: Trap
title: MADCTL row/column swap needs the size swapped too
description: pixel_direction_vertical exchanges the controller's axes, but width and height come from the configuration, which must be set to match.
tags: [trap, madctl, orientation]
status: draft
generated: { by: claude-opus-5/claude-code, at: "2026-09-16T00:00:00Z" }
sources:
  - id: api
    resource: src/ST7789/Concerns/ST7789API.php
    title: setMADControl() / setAddressWindow()
---

# Trap

`setMADControl()` only writes 0x36; `width()`/`height()` and full-panel windows read configuration.[^api] MV on + portrait size → windows clipped or wrapped.

# Use instead

Landscape: `width: 320, height: 240, mad_ctrl: new ST7789MADControl(pixel_direction_vertical: true)`. ST7796 default MADCTL already MV → default size 480×320.

[^api]: setMADControl() / setAddressWindow()
