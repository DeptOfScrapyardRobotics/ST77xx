---
type: Guide
title: Drawing frames
description: Pack bytes from a panel's FormatSpec, write them into a window with transmit(), apply offsets, or fill with fill().
tags: [drawing, formatspec, transmit, rgb565]
status: draft
generated: { by: claude-opus-5/claude-code, at: "2026-09-16T00:00:00Z" }
sources:
  - id: panel
    resource: src/ST7789/ST7789.php
    title: ST7789::transmit() / generateFormatSpec()
  - id: api
    resource: src/ST7789/Concerns/ST7789API.php
    title: setAddressWindow()
  - id: fill
    resource: src/ST77xxFills.php
    title: ST77xxFills
---

# FormatSpec

`ROW_MAJOR`, `BitDepth::from(color_mode->bitsPerPixel())`, `TOP_TO_BOTTOM`, `Endianness::MSB`.[^panel] Built in ctor; rebuilt by `setPixelFormat()`.

16-bit: 2 bytes/pixel, high first. Row by row, left → right. 320×240 = 153 600 bytes.

# transmit()

`transmit(int $origin_x, int $origin_y, array $raw_data, ?int $frame_width = null, ?int $frame_height = null)` → `setAddressWindow()` → `2C` → `data()`.[^panel]

Window:[^api] `2A x0h x0l x1h x1l`, `2B y0h y0l y1h y1l`; `x0 = x + x_offset`, `x1 = x0 + w − 1`, same for y.

# fill()

`fill(int $red, int $green, int $blue)`, channels 0–255 else `invalidRegisterValue`. Unit from current `formatSpec()->bit_depth`:[^fill]

| Depth | Unit | Bytes |
|---|---|---|
| B12 | pixel pair | `RG BR GB` nibbles; odd count padded |
| B16 | pixel | RGB565 high, low |
| B18 | pixel | `R&FC G&FC B&FC` |

Full window, `2C`, chunks of whole units ≤ `max_packet_size` (min one unit; transport re-splits if packet < unit).

# Mode switching

`setPixelFormat()` / `color_mode` any time → COLMOD + FormatSpec rebuilt → next `fill()` / packer follows. No depth favoured.

# Live reference

FT232H 10 MHz, 2048-byte packets, 320×240, 16-bit: fill ~270 ms, frame 279 ms, 100×60 window 32 ms.

# Related

* [settings](/settings.md) · [controllers](/controllers.md)

[^panel]: ST7789::transmit() / generateFormatSpec()
[^api]: setAddressWindow()
[^fill]: ST77xxFills
