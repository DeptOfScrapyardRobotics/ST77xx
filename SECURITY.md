# Security Policy

## Supported versions

st77xx is pre-1.0. No 0.x release receives security fixes or advisories; fixes land in the
next release line. Security support starts with 1.0.

| Version | Security fixes |
|---------|----------------|
| < 1.0   | No             |

## Reporting a vulnerability

Please don't open a public issue for a security problem.

Report it privately through GitHub: the **Report a vulnerability** button on this repository's
**Security** tab. If that isn't available, email **info@projectsaturnstudios.com**.

Include what you found, the affected version, the adapter and hardware in use, and steps to
reproduce. Reports are read and weighed for the release line in development; before 1.0 there is
no response-time commitment.

## Security model

st77xx is plain PHP. It writes ST7735, ST7789 and ST7796 commands and pixel data through the bus
and pin transports `scrapyard-io/framework` hands it, and holds no native code of its own. What a
PHP process may touch is decided below it: the adapter (`microscrap/scrapyard-linux` over
ext-posi, `microscrap/scrapyard-usb` over ext-ftdi) and the operating system's permissions on the
SPI, GPIO or USB device. Grant those through device groups or udev rules scoped to the hardware,
not by running PHP as root.

- **Configuration is trusted input.** `conjure()` connects whatever bus, chip select and DC / RST
  pins the `circuits.st7735` / `st7789` / `st7796` config names, drives those pins as outputs, and
  clocks the chip select at the configured speed. Keep that config under the app's control: a wrong
  pin number drives another device's line.
- **Writes are checked.** Register values outside their ranges throw before anything is written, a
  shared bus in another SPI mode is refused, and a refused or short bus write throws instead of
  being ignored.
- **Frame bytes are not validated.** `transmit()` sends the bytes it is given; the panel shows them.

A report is in scope when this package writes a command or a bus frame it was not asked to, drives
a pin other than its configured DC and RST, or lets a well-formed call leave the controller in a
state its configuration does not describe. Weaknesses in an adapter or extension belong to that
package's own policy.
