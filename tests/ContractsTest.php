<?php

use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\ST7735;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\ST7789;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\ST7796;
use GeneralPurposeIO\Contracts\IntegratedCircuits\DisplayPanel;
use GeneralPurposeIO\Contracts\IntegratedCircuits\RefreshesOnCommand;
use GeneralPurposeIO\Contracts\IntegratedCircuits\Switchable;
use GeneralPurposeIO\Contracts\IntegratedCircuits\WindowAddressable;

it('is a window-addressable, switchable display panel that does not refresh on command', function (string $class): void {
    expect(is_subclass_of($class, DisplayPanel::class))->toBeTrue()
        ->and(is_subclass_of($class, WindowAddressable::class))->toBeTrue()
        ->and(is_subclass_of($class, Switchable::class))->toBeTrue()
        ->and(is_subclass_of($class, RefreshesOnCommand::class))->toBeFalse();
})->with([ST7735::class, ST7789::class, ST7796::class]);
