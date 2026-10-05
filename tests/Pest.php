<?php

/*
| Proven against recording fakes: every command and data byte a panel would
| see, tagged by the DC level it went out under, plus every RST level. Boot
| sequences are pinned to the datasheet init values. Nothing here touches a
| bus. The live checks are a 480×320 ST7796 on a Raspberry Pi's SPI and a
| 240×320 ST7789 on an FT232H's SPI.
*/

use DeptOfScrapyardRobotics\Displays\ST77xx\Tests\Support\ConfigPathVessel;
use DeptOfScrapyardRobotics\Displays\ST77xx\Tests\Support\FakeDigitalIOConnectionDriver;
use DeptOfScrapyardRobotics\Displays\ST77xx\Tests\Support\FakeSPIConnectionDriver;
use GeneralPurposeIO\Digital\DigitalOConnectionManager;
use GeneralPurposeIO\IntegratedCircuits\CircuitRegistry;
use GeneralPurposeIO\SPI\SPIConnectionManager;
use Voyager\Config\Repository;
use Voyager\Vessel\ControlPanel;

/*
| A Venusian app's core defines config() over the container's config repository;
| CircuitRegistry::conjure() calls it. A package suite has no core, so this
| stands in for it the same way.
*/
if (! function_exists('config')) {
    function config(array|string|null $key = null, mixed $default = null): mixed
    {
        $config = ControlPanel::getInstance()->make('config');

        return match (true) {
            is_null($key) => $config,
            is_array($key) => $config->set($key),
            default => $config->get($key, $default),
        };
    }
}

/**
 * The shared container as an app sets it up: config, the circuit catalog, and the two protocol managers, each
 * with a 'fake' driver.
 *
 * @return array{spi: FakeSPIConnectionDriver, digital: FakeDigitalIOConnectionDriver, app: ConfigPathVessel}
 */
function fakeBench(array $config = []): array
{
    $app = new ConfigPathVessel;
    $app->registerInstance('config', new Repository($config));
    $app->registerInstance('circuit', new CircuitRegistry);
    ControlPanel::setInstance($app);

    $bench = [
        'spi' => new FakeSPIConnectionDriver,
        'digital' => new FakeDigitalIOConnectionDriver,
        'app' => $app,
    ];

    $app->registerInstance('gpio.spi', (new SPIConnectionManager($app))->extend('fake', fn () => $bench['spi']));
    $app->registerInstance('gpio.digital', (new DigitalOConnectionManager($app))->extend('fake', fn () => $bench['digital']));

    return $bench;
}

pest()->afterEach(function (): void {
    ControlPanel::setInstance(null);
})->in(__DIR__);
