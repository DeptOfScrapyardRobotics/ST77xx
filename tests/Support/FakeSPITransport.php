<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\Tests\Support;

use GeneralPurposeIO\SPI\SPITransport;

/** Records every write, tagged with the DC level it went out under. */
final class FakeSPITransport extends SPITransport
{
    /** @var list<array{0: string, 1: list<int>}> ['cmd'|'data', bytes] */
    public array $writes = [];

    public function __construct(public readonly FakeOutputPin $dc)
    {
        parent::__construct(0);
    }

    public function handle(): string { return 'fake'; }
    public function read(int $len): array|false { return false; }
    public function transfer(array|string $data): array|false { return false; }
    public function close(): void {}

    public function write(array|string $data): int
    {
        $bytes = is_array($data) ? array_values($data) : array_values(unpack('C*', $data));
        $this->writes[] = [$this->dc->state ? 'data' : 'cmd', $bytes];

        return count($bytes);
    }

    /**
     * Commands with their parameter bytes joined: [[register, [params...]], ...].
     * Data writes that follow a command attach to it, the way the panel reads them.
     *
     * @return list<array{0: int, 1: list<int>}>
     */
    public function commands(int $from = 0): array
    {
        $out = [];

        foreach (array_slice($this->writes, $from) as [$kind, $bytes]) {
            if ($kind === 'cmd') {
                foreach ($bytes as $byte) {
                    $out[] = [$byte, []];
                }

                continue;
            }

            $out[array_key_last($out)][1] = [...$out[array_key_last($out)][1], ...$bytes];
        }

        return $out;
    }
}
