<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\Transports;

use GeneralPurposeIO\Contracts\IntegratedCircuits\DataCommander;

abstract class ST77xxDataTransport implements DataCommander
{
    public function __construct(
        protected int $max_packet_size
    ) {}

    abstract protected function closeMain(): void;
    abstract protected function sendData(array|string $data = []): void;
    abstract protected function sendCommand(int $register, array $command_data = []): int;

    public function command(int $register, array $command_data = []): int
    {
        return $this->sendCommand($register, $command_data);
    }

    public function data(array|string $data = []): void
    {
        $this->sendData($data);
    }

    public function maxPacketSize(int $size): static
    {
        $this->max_packet_size = $size;
        return $this;
    }

    public function reset(int $sleep_time): void {}

    public function close(): void
    {
        $this->closeMain();
    }
}