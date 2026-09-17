---
type: Trap
title: MPSSE SPI speed comes from clockRate()
description: On the usb adapter the SPI factory opens its context at clockRate(), so speed() has no effect and the default is 400 kHz.
tags: [trap, spi, mpsse, speed]
status: draft
generated: { by: claude-opus-5/claude-code, at: "2026-09-16T00:00:00Z" }
sources:
  - id: factory
    resource: microscrap/scrapyard-usb:src/SPI/MpsseSPIConnectionFactory.php
    title: MpsseSPIConnectionFactory::getHandle()
---

# Trap

`MpsseSPIConnectionFactory::getHandle()` passes `clock_rate` (default `FOUR_HUNDRED_KHZ`) to `mpsse_open`; `speed` from the base factory unused.[^factory] 320×240 frame at 400 kHz ≈ several seconds.

# Use instead

`->clockRate(MPSSEClockRate::TEN_MHZ)` on usb; `->speed(...)` on native.

[^factory]: MpsseSPIConnectionFactory::getHandle()
