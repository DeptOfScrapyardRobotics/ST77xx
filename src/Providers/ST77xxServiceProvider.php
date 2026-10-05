<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\Providers;

use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\ST7735;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\ST7789;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\ST7796;
use Voyager\NutsAndBolts\ServiceProvider;

/**
 * Each panel's wiring config lives under the circuits tree: config('circuits.st7789'),
 * published to config/circuits/st7789.php, which the config loader keys the same way.
 */
class ST77xxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__, 2).'/config/st7735.php', 'circuits.st7735');
        $this->mergeConfigFrom(dirname(__DIR__, 2).'/config/st7789.php', 'circuits.st7789');
        $this->mergeConfigFrom(dirname(__DIR__, 2).'/config/st7796.php', 'circuits.st7796');
    }

    public function boot(): void
    {
        $this->publishes([
            dirname(__DIR__, 2).'/config/st7735.php' => $this->app->configPath('circuits/st7735.php'),
            dirname(__DIR__, 2).'/config/st7789.php' => $this->app->configPath('circuits/st7789.php'),
            dirname(__DIR__, 2).'/config/st7796.php' => $this->app->configPath('circuits/st7796.php'),
        ], 'st77xx-config');

        // With the GPIO catalog installed, each chip is conjurable by slug:
        // app('circuit')->conjure('st7789').
        if ($this->app->isBound('circuit')) {
            $catalog = $this->app->make('circuit');
            $catalog->addCircuit('st7735', ST7735::class);
            $catalog->addCircuit('st7789', ST7789::class);
            $catalog->addCircuit('st7796', ST7796::class);
        }
    }
}
