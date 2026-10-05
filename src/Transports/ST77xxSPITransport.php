<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\Transports;

use GeneralPurposeIO\Contracts\Digital\DigitalOutTransport;
use GeneralPurposeIO\Contracts\SPI\SPITransport;
use GeneralPurposeIO\Contracts\SPI\WritesFromMemory;

/** 4-wire SPI: DC low for the command byte, high for its parameters and for pixel data; RST pulses on boot. */
class ST77xxSPITransport extends ST77xxDataTransport
{
    public function __construct(
        protected SPITransport $transport,
        protected DigitalOutTransport $dc,
        protected DigitalOutTransport $rst,
        int $max_packet_size = 2048
    ) {
        parent::__construct($max_packet_size);
    }

    public function reset(int $sleep_time): void
    {
        $this->rst->high();
        usleep($sleep_time);

        $this->rst->low();
        usleep($sleep_time);

        $this->rst->high();
        usleep($sleep_time);
    }

    public function beginData(): void
    {
        $this->dc->high();
    }

    public function memoryBus(): ?WritesFromMemory
    {
        return $this->transport instanceof WritesFromMemory ? $this->transport : null;
    }

    protected function closeMain(): void
    {
        $this->dc->close();
        $this->rst->close();
    }

    protected function sendData(array|string $data = []): void
    {
        $this->dc->high();

        if (is_string($data)) {
            foreach (str_split($data, $this->max_packet_size) as $chunk) {
                $this->checked('data', strlen($chunk), $this->transport->write($chunk));
            }

            return;
        }

        foreach (array_chunk($data, $this->max_packet_size) as $chunk) {
            $this->checked('data', count($chunk), $this->transport->write($chunk));
        }
    }

    protected function sendCommand(int $register, array $command_data = []): int
    {
        $this->dc->low();
        $results = $this->checked(sprintf('command 0x%02X', $register), 1, $this->transport->write([$register]));

        if (count($command_data) > 0) {
            $this->sendData($command_data);
        }

        return $results;
    }
}
