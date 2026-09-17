<?php

use DeptOfScrapyardRobotics\Displays\ST77xx\Providers\ST77xxServiceProvider;
use DeptOfScrapyardRobotics\Displays\ST77xx\Tests\Support\ConfigPathVessel;
use Voyager\Config\Repository;
use Voyager\NutsAndBolts\ServiceProvider;
use Voyager\Vessel\Vessel;

it('registers each panel\'s wiring config under circuits, keeping anything the app already set', function (): void {
    $vessel = new Vessel;
    $vessel->instance('config', new Repository(['circuits' => [
        'adxl345' => ['default_config' => 'i2c'],
        'st7789' => ['configs' => ['spi' => ['chip_select' => 1]]],
    ]]));

    (new ST77xxServiceProvider($vessel))->register();

    $config = $vessel->make('config');

    expect($config->get('circuits.st7735.default_config'))->toBe('spi')
        ->and($config->get('circuits.st7796.configs.spi.rst.pin'))->toBe(1)
        ->and($config->get('circuits.st7789.configs.spi.chip_select'))->toBe(1)
        ->and($config->get('circuits.adxl345'))->toBe(['default_config' => 'i2c'])
        ->and($config->has('st7789'))->toBeFalse();
});

it('publishes the three configs into config/circuits under the st77xx-config tag', function (): void {
    $app = new ConfigPathVessel('/app/config');
    $app->instance('config', new Repository);

    $provider = new ST77xxServiceProvider($app);
    $provider->register();
    $provider->boot();

    $root = dirname(__DIR__, 2);

    expect(ServiceProvider::pathsToPublish(ST77xxServiceProvider::class, 'st77xx-config'))->toBe([
        "{$root}/config/st7735.php" => '/app/config/circuits/st7735.php',
        "{$root}/config/st7789.php" => '/app/config/circuits/st7789.php',
        "{$root}/config/st7796.php" => '/app/config/circuits/st7796.php',
    ]);
});
