<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\Transports;

use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;
use GeneralPurposeIO\Contracts\IntegratedCircuits\DataCommander;
use GeneralPurposeIO\Contracts\SPI\WritesFromMemory;

/**
 * Commands and pixel bytes to the panel. Every write is checked: a short or failed write throws instead of being
 * ignored. SPI has no acknowledge, so a missing panel still cannot be detected from the bus.
 */
abstract class ST77xxDataTransport implements DataCommander
{
    public function __construct(
        protected int $max_packet_size
    ) {
        $this->maxPacketSize($max_packet_size);
    }

    abstract protected function closeMain(): void;
    abstract protected function sendData(array|string $data = []): void;
    abstract protected function sendCommand(int $register, array $command_data = []): int;

    /** Raise the data line: what follows is data, as pixel bytes after a memory write command. */
    abstract public function beginData(): void;

    /** The bus pixel bytes can go out on straight from memory; null when this transport has none. */
    public function memoryBus(): ?WritesFromMemory
    {
        return null;
    }

    public function command(int $register, array $command_data = []): int
    {
        return $this->sendCommand($register, $command_data);
    }

    public function data(array|string $data = []): void
    {
        $this->sendData($data);
    }

    /** Bytes per data write; SPI adapters split a longer write into the bus's own message size. */
    public function maxPacketSize(int $size): static
    {
        if ($size < 1) {
            throw ST77xxException::invalidPacketSize($size);
        }

        $this->max_packet_size = $size;

        return $this;
    }

    public function reset(int $sleep_time): void {}

    public function close(): void
    {
        $this->closeMain();
    }

    /** @throws ST77xxException when the bus wrote fewer bytes than asked */
    protected function checked(string $what, int $expected, int $written): int
    {
        if ($written !== $expected) {
            throw ST77xxException::spiWriteFailed($what, $expected, $written);
        }

        return $written;
    }
}
