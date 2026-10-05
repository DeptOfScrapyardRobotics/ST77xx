---
okf_version: "0.2"
---

# dept-of-scrapyard-robotics/st77xx

ST7735, ST7789 and ST7796 colour TFT drivers for `scrapyard-io/framework` 0.10. SPI + DC/RST, conjured from config, datasheet boot from per-chip configuration objects, checked writes, row-major `FormatSpec`, windowed RAM writes, rotation that carries size and offsets, fill in any colour mode.

Read this index first, open only concepts task needs. Every concept `status: draft` until human verifies.

# Concepts

* [Package](overview.md) - ST7735, ST7789 and ST7796 TFT drivers for scrapyard-io/framework 0.10 — identity, requires, shared class shape, errors.
* [Connecting](connecting.md) - conjure() and the spi() factory, SPI mode and clock, sharing a bus, DC and RST, building the transport by hand.
* [Controllers](controllers.md) - Per-controller boot sequences as sent, configuration fields and defaults.
* [Drawing](drawing.md) - FormatSpec, packing with a Surface framebuffer, transmit() windows, fill(), colour modes, live timings.
* [Settings](settings.md) - Properties and setters, MADCTL, orientation with size and offsets following.
* [Wiring config](wiring-config.md) - circuits.st7735 / st7789 / st7796 keys, how conjure() reads them, publish tag.

# Runbooks

* [Hardware smoke](runbooks/hardware-smoke.md) - Pi 5 ST7796 on spidev, FT232H ST7789 over MPSSE, scratch script booted through the real providers.

# Log

* [log.md](log.md)
