<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\Tests\Support;

use GeneralPurposeIO\Digital\DigitalIOConnectionFactory;

final class FakeDigitalIOConnectionFactory extends DigitalIOConnectionFactory
{
    protected function device(): string|int
    {
        return $this->device;
    }

    protected function getHandle(): string
    {
        return "gpio:{$this->device}";
    }
}
