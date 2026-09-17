---
type: Trap
title: SPI writes are unchecked
description: ST77xxSPITransport ignores the result of data writes, so a disconnected panel produces no error.
tags: [trap, transport, errors]
status: draft
generated: { by: claude-opus-5/claude-code, at: "2026-09-16T00:00:00Z" }
sources:
  - id: transport
    resource: src/Transports/ST77xxSPITransport.php
    title: ST77xxSPITransport
---

# Trap

`sendData()` discards write results; `command()` returns the register write result without throwing.[^transport] SPI has no ACK, so an unplugged panel boots "fine". `ST77xxException::spiWriteFailed()` exists, unused.

# Use instead

Check `transport()->command(...)` result where it matters; confirm visually.

[^transport]: ST77xxSPITransport
