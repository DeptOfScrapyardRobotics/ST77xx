<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx;

use GeneralPurposeIO\Contracts\IntegratedCircuits\CircuitException;

class ST77xxException extends CircuitException
{
    public static function transportMissingProtocol(): static
    {
        return new static('All ST77xx Displays require an SPI capable connection.');
    }

    public static function missingDigitalPins(): static
    {
        return new static('All ST77xx Displays require SPI connections to enable DC and RST DigitalOutput pins.');
    }

    public static function invalidRegisterValue(string $field, int $value, int $min, int $max): static
    {
        return new static("Valid $field values are between $min and $max, you input $value.");
    }

    public static function spiWriteFailed(string $what, int $expected, int $written): static
    {
        return new static("ST77xx SPI {$what} write failed: {$written} of {$expected} bytes. Check wiring, SPI bus permissions, and that the device is powered.");
    }

    public static function invalidSpiMode(int $mode): static
    {
        return new static("SPI mode {$mode} does not exist; use 0 to 3.");
    }

    public static function invalidSpiClock(int $hz): static
    {
        return new static("ST77xx SPI clock {$hz} Hz must be at least 1 Hz.");
    }

    public static function wrongSpiMode(string|int $device, int $bus_mode, int $mode): static
    {
        return new static("SPI bus [{$device}] runs in mode {$bus_mode}; this panel is configured for mode {$mode}.");
    }

    public static function incompletePin(string $name): static
    {
        return new static("ST77xx needs its {$name} pin as driver, device and pin.");
    }

    public static function invalidPacketSize(int $size): static
    {
        return new static("ST77xx max_packet_size {$size} must be at least 1.");
    }

    public static function invalidProperty(string $name, string $class): static
    {
        return new static("Invalid property '{$name}' on {$class}.");
    }

    public static function notConnected(string $protocol, string $driver, string|int $device): static
    {
        return new static("ST77xx could not get a {$protocol} connection to [{$device}] from the '{$driver}' driver. Check circuits.<chip>.configs and that the adapter package is installed.");
    }

    public static function invalidColorMode(int $bits): static
    {
        return new static("Invalid color mode: {$bits}");
    }
}
