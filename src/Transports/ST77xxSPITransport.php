<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\Transports;

use GeneralPurposeIO\Contracts\Digital\DigitalOutTransport;
use GeneralPurposeIO\Contracts\SPI\SPITransport;

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

    protected function closeMain(): void
    {
        $this->dc->close();
        $this->rst->close();
    }

    protected function sendData(array|string $data = []): void
    {
        if (is_string($data)) {
            $length = strlen($data);
            $offset = 0;

            while ($offset < $length) {
                $this->dc->high();
                $this->transport->write(substr($data, $offset, $this->max_packet_size));
                $offset += $this->max_packet_size;
            }

            return;
        }

        foreach (array_chunk($data, $this->max_packet_size) as $chunk) {
            $this->dc->high();
            $this->transport->write($chunk);
        }
    }

    protected function sendCommand(int $register, array $command_data = []): int
    {
        $this->dc->low();
        $results = $this->transport->write([$register]);
        if(count($command_data) > 0){
            $this->sendData($command_data);
        }

        return $results;
    }
}