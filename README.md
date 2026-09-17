# st77xx

Drive ST7735, ST7789 and ST7796 colour TFT panels from PHP over SPI, using the ScrapyardIO GPIO framework.

`dept-of-scrapyard-robotics/st77xx` boots each controller with its datasheet init sequence, keeps its settings in a configuration object, and writes pixel bytes into any window of panel memory. Each panel describes its byte format through a `FormatSpec`, so a Surface CPU engine, or your own code, can render for it.

| Controller | Class | Default size | Typical panels |
|---|---|---|---|
| ST7735 | `ST7735\ST7735` | 128×128 | 1.44" and 1.8" TFTs |
| ST7789 | `ST7789\ST7789` | 240×240 | 1.3" to 2.4" IPS TFTs |
| ST7796 | `ST7796\ST7796` | 480×320 | 3.5" and 4" TFTs |

## Requirements

- PHP 8.4 or newer
- A Venusian application with `scrapyard-io/framework` 0.8
- An adapter for your hardware:
  - `microscrap/scrapyard-usb` for FTDI MPSSE boards such as the FT232H (needs `ext-ftdi`)
  - `microscrap/scrapyard-linux` for native `spidev` and `libgpiod` (needs `ext-posi`)

## Installation

```bash
composer require dept-of-scrapyard-robotics/st77xx
```

The service provider is discovered automatically. It merges the package's wiring config under `circuits.st7735`, `circuits.st7789` and `circuits.st7796`. To publish those files into your app, run:

```bash
php computer vendor:publish --tag=st77xx-config
```

That writes `config/circuits/st7735.php`, `config/circuits/st7789.php` and `config/circuits/st7796.php`.

## Quick start

A 240×320 ST7789 in landscape on an FT232H, with DC on GPIOL1 and RST on GPIOL2:

```php
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789MADControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\ST7789;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\ST7789Configuration;
use DeptOfScrapyardRobotics\Displays\ST77xx\Transports\ST77xxSPITransport;
use GeneralPurposeIO\Digital\DigitalIO;
use GeneralPurposeIO\SPI\SPI;
use Microscrap\Bindings\MPSSE\Enums\MPSSEClockRate;

$spi = SPI::driver('usb')
    ->connectTo('ft232h')
    ->mode(0)
    ->clockRate(MPSSEClockRate::TEN_MHZ)
    ->register()
    ->device('ft232h', 0);

$pins = DigitalIO::driver('usb');
$dc = $pins->output('ft232h', 1);
$rst = $pins->output('ft232h', 2);

$panel = new ST7789(
    new ST77xxSPITransport($spi, $dc, $rst),
    new ST7789Configuration(
        width: 320,
        height: 240,
        mad_ctrl: new ST7789MADControl(pixel_direction_vertical: true),
    ),
    boot_now: true,
);

$panel->fill(255, 0, 0);   // red
```

Booting pulses RST, runs the controller's init sequence and turns the display on.

## Connecting

Every panel takes an `ST77xxSPITransport`, built from one SPI device and two output pins. DC selects between command and data, and RST resets the panel at boot.

### FTDI MPSSE

On an FT232H the SPI context also serves GPIO, so the DC and RST pins come from the same board:

```php
$spi = SPI::driver('usb')->connectTo('ft232h')->mode(0)->clockRate(MPSSEClockRate::TEN_MHZ)->register()->device('ft232h', 0);

$dc = DigitalIO::driver('usb')->output('ft232h', 1);    // GPIOL1 (D5)
$rst = DigitalIO::driver('usb')->output('ft232h', 2);   // GPIOL2 (D6)
```

Chip select is D3. Set the bus speed with `clockRate()`; the MPSSE factory takes its clock from there.

### Linux spidev

```php
$spi = SPI::driver('native')->connectTo(0)->mode(0)->speed(40_000_000)->register()->device(0, 0);

$pins = DigitalIO::driver('native')->connectTo(0)->register();
$dc = $pins->output(0, 24);
$rst = $pins->output(0, 25);
```

### The transport

```php
$transport = new ST77xxSPITransport($spi, $dc, $rst, max_packet_size: 2048);
```

A command goes out with DC low, and its parameters and pixel data with DC high. Data is written in chunks of `max_packet_size` bytes; each panel sets this from its configuration at boot.

### From the published config

The config files hold your wiring. The package merges them but does not open connections from them, so read them where you build the panel:

```php
$name = config('circuits.st7789.default_config');      // 'spi'
$wiring = config("circuits.st7789.configs.{$name}");

$spi = SPI::driver($wiring['driver'])->connectTo($wiring['device'])->register()->device($wiring['device'], $wiring['chip_select']);
$dc = DigitalIO::driver($wiring['dc']['driver'])->output($wiring['dc']['device'], $wiring['dc']['pin']);
$rst = DigitalIO::driver($wiring['rst']['driver'])->output($wiring['rst']['device'], $wiring['rst']['pin']);
```

On an FT232H the SPI connection already registered the board for its pins. For native pins, connect the gpiochip with `DigitalIO::driver('native')->connectTo(0)->register()` before asking for them.

## Configuration objects

Each controller has its own configuration class. Every argument is optional, and the defaults reproduce the common init sequence for that controller.

Shared by all three:

| Argument | Meaning |
|---|---|
| `width`, `height` | panel size in the orientation you set with `mad_ctrl` |
| `max_packet_size` | largest data write, in bytes |
| `x_offset`, `y_offset` | where the visible area starts in controller memory |
| `mad_ctrl` | orientation and colour order (MADCTL) |
| `color_mode` | 12-, 16- or 18-bit pixels; defaults to 16-bit RGB565 |
| `invert_display` | display inversion at boot |
| `gamma_positive`, `gamma_negative` | gamma tables |

| Controller | Defaults | Controller-specific arguments |
|---|---|---|
| `ST7735Configuration` | 128×128, 2048-byte packets, MADCTL `0xC8`, inversion off | `nfc`, `ifc`, `pfc` (frame rate), `inversion_control`, `power_control_1` to `power_control_5`, `v_com_ctrl` |
| `ST7789Configuration` | 240×240, 2048-byte packets, MADCTL `0x00`, inversion on | `porch_ctrl`, `gate_ctrl`, `v_com_ctrl`, `lcm_ctrl`, `vdv_vrh_enable`, `vrh`, `vdv`, `frame_rate_ctrl`, `power_control_1` |
| `ST7796Configuration` | 480×320, 4092-byte packets, MADCTL `0x28`, inversion off | `inversion_ctrl`, `display_fn_ctrl`, `output_adjust`, `power_control_2`, `power_control_3`, `v_com_ctrl` |

Each register argument is a breakout object from that controller's `Breakouts` namespace, such as `ST7789MADControl` or `ST7735PowerControl1`.

### Orientation

`mad_ctrl` sets the scan direction. Setting `pixel_direction_vertical` swaps rows and columns, so swap `width` and `height` to match:

```php
// 240×320 panel, portrait
new ST7789Configuration(width: 240, height: 320);

// same panel, landscape
new ST7789Configuration(width: 320, height: 240, mad_ctrl: new ST7789MADControl(pixel_direction_vertical: true));
```

The other MADCTL flags mirror the image (`bottom_top_row_addresses`, `right_left_column_addresses`) and switch RGB to BGR (`bgr_order_mode`).

### Offsets

Some panels show only part of the controller's memory. A 240×240 ST7789 rotated 180° starts 80 rows down, and a 135×240 panel sits 52 columns and 40 rows in:

```php
new ST7789Configuration(width: 135, height: 240, x_offset: 52, y_offset: 40);
```

The offsets are added to every window the panel opens.

## Drawing

The panel does not keep pixels. You send it bytes already packed the way `formatSpec()` describes:

```php
$spec = $panel->formatSpec();
// pixel_format ROW_MAJOR, bit_depth B16, scan_direction TOP_TO_BOTTOM, endianness MSB
```

Rows run top to bottom, pixels left to right, and in 16-bit mode each pixel is two bytes, high byte first. A 320×240 frame is 153,600 bytes:

```php
$row = '';
for ($x = 0; $x < $panel->width(); $x++) {
    $rgb565 = colourAt($x);
    $row .= chr($rgb565 >> 8).chr($rgb565 & 0xFF);
}

$panel->transmit(0, 0, array_values(unpack('C*', str_repeat($row, $panel->height()))));
```

`transmit($x, $y, $bytes, $width, $height)` opens a window over that rectangle, starts a memory write, and streams the bytes. Width and height default to the whole panel. To redraw part of the screen, send only that window:

```php
$panel->transmit(110, 90, $box_bytes, 100, 60);   // 100×60 at (110, 90)
```

`fill($red, $green, $blue)` paints the whole panel one colour without building a frame. Channels are 0 to 255, and the fill packs them for the colour mode the panel is in right now:

```php
$panel->fill(0, 255, 0);   // green in 12-, 16- or 18-bit mode alike
```

| Mode | Bytes per pixel | Packing |
|---|---|---|
| 12-bit | 1.5 | RGB444, two pixels per three bytes |
| 16-bit | 2 | RGB565, high byte first |
| 18-bit | 3 | RGB666, each channel in the top six bits of its byte |

Data goes out in whole pixels, as many as fit in `max_packet_size`.

Measured on an FT232H at 10 MHz with 2048-byte packets:

| Operation | Time |
|---|---|
| Boot | 208 ms |
| Full 320×240 fill, 16-bit | about 270 ms |
| Full 320×240 frame | 279 ms |
| 100×60 window | 32 ms |

## Settings

```php
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Enums\ST7789ColorMode;

$panel->display_on = false;             // blank
$panel->display_on = true;
$panel->sleep_mode_on = true;           // low power
$panel->sleep_mode_on = false;          // waits 120 ms
$panel->invert_display = false;
$panel->color_mode = ST7789ColorMode::COLOR18;
$panel->mad_ctrl = new ST7789MADControl(right_left_column_addresses: true);
```

Any configuration key reads as a property: `$panel->width`, `$panel->x_offset`, `$panel->gamma_positive` and so on. Every register argument listed under [Configuration objects](#configuration-objects) can also be written as a property, and the panel sends it straight away. Size, offsets and packet size are read-only. The driver never reads the controller, so reads return what the configuration holds.

Changing `color_mode` also rebuilds the `FormatSpec`, so check it again before packing the next frame. You can switch between 12-, 16- and 18-bit at any time; `fill()` and the `FormatSpec` always follow the mode in force.

The same settings are available as methods, such as `setDisplay()`, `setSleepMode()`, `setDisplayInversion()`, `setPixelFormat()` (which also takes 12, 16 or 18), `setMADControl()` and `setAddressWindow()`, plus one setter per register breakout.

## Errors

Failures throw `DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException`, which extends the framework's `GPIOLevelException`:

- A register value or fill channel is out of range, for example a porch or VCOM setting.
- A colour mode other than 12, 16 or 18 bits is requested.
- Your code reads or writes a property or configuration key that doesn't exist, or writes a read-only one.

## Closing

```php
$panel->close();
```

`close()` releases the DC and RST pins. The SPI connection belongs to the protocol driver and stays open.

## Configuration files

Each of `config/circuits/st7735.php`, `st7789.php` and `st7796.php` has this shape:

| Key | Default | Meaning |
|---|---|---|
| `default_config` | `'spi'` | which entry under `configs` to use |
| `configs.spi.driver` | `'none'` | SPI adapter: `usb` or `native` |
| `configs.spi.device` | `''` | `ft232h`, or a spidev master number |
| `configs.spi.chip_select` | `0` | chip select |
| `configs.spi.dc` / `rst` | pins 0 / 1 | `driver`, `device` and `pin` for each line |

## Testing

```bash
composer install
vendor/bin/pest
```

The suite runs against recording fakes of the SPI bus and pins, so it needs no hardware. Each controller's boot sequence is checked byte for byte against its datasheet init values.

## License

MIT. See [LICENSE](LICENSE).
