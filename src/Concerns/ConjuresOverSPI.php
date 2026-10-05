<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\Concerns;

use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;
use DeptOfScrapyardRobotics\Displays\ST77xx\Transports\ST77xxSPITransport;
use GeneralPurposeIO\Contracts\Digital\DigitalOutTransport;
use GeneralPurposeIO\Contracts\SPI\SPIMode;
use Voyager\Vessel\ControlPanel;

/**
 * The spi() protocol factory the circuit catalog calls. Its parameters are the
 * keys of a config/circuits/<chip>.php entry, so app('circuit')->conjure('st7789')
 * builds a wired, booted panel from the app's config alone. One shape for all
 * three controllers. A bus or pin device that is not connected yet is connected
 * here; one the app already connected is shared as it is.
 */
trait ConjuresOverSPI
{
    /** @return class-string */
    abstract protected static function configurationClass(): string;

    /** @return class-string */
    abstract protected static function madControlClass(): string;

    /**
     * Opens the bus in $mode when it is not connected yet and clocks this chip select at $speed whatever the bus
     * runs at; a bus the app already opened in another mode is refused. DC and RST are opened after the bus, so
     * pins on an FT232H ride the bus's own context. Null geometry, offsets and inversion keep the controller's
     * defaults.
     *
     * @param  array{driver: string, device: string|int, pin: int}  $dc
     * @param  array{driver: string, device: string|int, pin: int}  $rst
     * @param  array<string, bool>  $mad_ctrl  named flags of the controller's MADControl breakout
     */
    public static function spi(
        string $driver,
        string|int $device,
        array $dc,
        array $rst,
        int $chip_select = 0,
        int $mode = 0,
        int $speed = 10_000_000,
        ?int $width = null,
        ?int $height = null,
        array $mad_ctrl = [],
        ?int $x_offset = null,
        ?int $y_offset = null,
        ?bool $invert_display = null,
        bool $boot_now = true,
    ): static {
        $spi_mode = SPIMode::tryFrom($mode) ?? throw ST77xxException::invalidSpiMode($mode);

        if ($speed < 1) {
            throw ST77xxException::invalidSpiClock($speed);
        }

        $bus = static::gpio('gpio.spi')->driver($driver);
        $spi = $bus->device($device, $chip_select)
            ?? $bus->connectTo($device)->mode($spi_mode)->speed($speed)->register()->device($device, $chip_select);

        if (is_null($spi)) {
            throw ST77xxException::notConnected('SPI', $driver, $device);
        }

        $bus_mode = $bus->settingsOf($device)?->mode;

        if (! is_null($bus_mode) && $bus_mode !== $spi_mode) {
            throw ST77xxException::wrongSpiMode($device, $bus_mode->value, $mode);
        }

        $spi->speed($speed);

        $configuration = static::configurationClass();
        $mad_control = static::madControlClass();
        $settings = array_filter(
            ['width' => $width, 'height' => $height, 'x_offset' => $x_offset, 'y_offset' => $y_offset, 'invert_display' => $invert_display],
            static fn (int|bool|null $value): bool => ! is_null($value),
        );

        if ($mad_ctrl !== []) {
            $settings['mad_ctrl'] = new $mad_control(...$mad_ctrl);
        }

        return new static(
            new ST77xxSPITransport($spi, static::line($dc, 'dc'), static::line($rst, 'rst')),
            new $configuration(...$settings),
            boot_now: $boot_now,
        );
    }

    /** @param  array{driver?: string, device?: string|int, pin?: int}  $line */
    protected static function line(array $line, string $name): DigitalOutTransport
    {
        if (! isset($line['driver'], $line['device'], $line['pin'])) {
            throw ST77xxException::incompletePin($name);
        }

        $pins = static::gpio('gpio.digital')->driver($line['driver']);
        $pin = $pins->output($line['device'], $line['pin'])
            ?? $pins->connectTo($line['device'])->register()->output($line['device'], $line['pin']);

        return $pin ?? throw ST77xxException::notConnected('DigitalIO', $line['driver'], $line['device']);
    }

    /** A protocol manager from the app's container: gpio.spi or gpio.digital. */
    protected static function gpio(string $manager): mixed
    {
        return ControlPanel::getInstance()->make($manager);
    }
}
