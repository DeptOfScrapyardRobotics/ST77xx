---
okf_version: "0.2"
---

# dept-of-scrapyard-robotics/st77xx — knowledge bundle

ST7735, ST7789 and ST7796 colour TFT drivers for `scrapyard-io/framework` 0.8. SPI + DC/RST, datasheet boot from per-chip configuration objects, row-major `FormatSpec`, windowed RAM writes, fill in any colour mode.

Read this index first, open only concepts task needs. Every concept `status: draft` until human verifies.

# Concepts

* [overview.md](/overview.md) - package identity, requires, class shape shared by the three chips, errors
* [connecting.md](/connecting.md) - ST77xxSPITransport, DC/RST, FT232H and spidev wiring
* [controllers.md](/controllers.md) - per-chip boot sequence, configuration fields, defaults
* [drawing.md](/drawing.md) - FormatSpec, packing, transmit() windows, offsets, mode-neutral fill(), colour-mode switching, timings
* [settings.md](/settings.md) - properties, setters, state in configuration, orientation
* [wiring-config.md](/wiring-config.md) - circuits.st77xx keys, publish tag

# Traps

* [traps/](/traps/index.md) - MPSSE speed via clockRate, MADCTL swaps width/height, unchecked writes

# Log

* [log.md](/log.md)
