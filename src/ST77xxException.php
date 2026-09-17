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

    public static function spiWriteFailed(string $kind, int $context): static
    {
        return new static("ST77xx SPI {$kind} write failed or wrote fewer bytes than expected (context: {$context}). Check wiring, SPI bus permissions, and that the device is powered.");
    }

    public static function invalidProperty(string $name, string $class): static
    {
        return new static("Invalid property '{$name}' on {$class}.");
    }

    public static function invalidColorMode(int $bits): static
    {
        return new static("Invalid color mode: {$bits}");
    }
}
