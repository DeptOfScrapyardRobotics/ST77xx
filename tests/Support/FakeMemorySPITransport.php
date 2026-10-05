<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\Tests\Support;

use GeneralPurposeIO\Contracts\SPI\WritesFromMemory;

/** A fake SPI bus that also writes from memory: each writeFrom() is recorded with the DC level it went out under. */
final class FakeMemorySPITransport extends FakeSPITransport implements WritesFromMemory
{
    /** @var list<array{0: string, 1: list<array{int, int}>}> ['cmd'|'data', spans] */
    public array $spans = [];

    public function writeFrom(array $spans): int
    {
        $this->spans[] = [$this->dc->state ? 'data' : 'cmd', $spans];

        return array_sum(array_column($spans, 1));
    }
}
