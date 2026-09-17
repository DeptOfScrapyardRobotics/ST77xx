<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Concerns;

use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Breakouts\ST7796GammaNegative;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Breakouts\ST7796GammaPositive;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;

trait ST7796Bootstrap
{
    use ST7796API;

    /**
     * Any configuration key reads as a property.
     *
     * @throws ST77xxException
     */
    public function __get(string $name): mixed
    {
        return $this->config()->get($name);
    }

    /**
     * @throws ST77xxException
     */
    public function __set(string $name, mixed $value): void
    {
        match ($name) {
            'display_on' => $this->setDisplay($value),
            'sleep_mode_on' => $this->setSleepMode($value),
            'invert_display' => $this->setDisplayInversion($value),
            'mad_ctrl' => $this->setMADControl($value),
            'color_mode' => $this->setPixelFormat($value),
            'inversion_ctrl' => $this->setDisplayInversionControl($value),
            'display_fn_ctrl' => $this->setDisplayFunctionControl($value),
            'output_adjust' => $this->setDisplayOutputCtrlAdjust($value),
            'power_control_2' => $this->setPowerControl2($value),
            'power_control_3' => $this->setPowerControl3($value),
            'v_com_ctrl' => $this->setVComControl($value),
            'gamma_positive' => $this->setGammaPositive($value),
            'gamma_negative' => $this->setGammaNegative($value),
            default => throw ST77xxException::invalidProperty($name, static::class),
        };
    }

    protected function _boot(): void
    {
        $config = $this->config();
        $this->transport()->maxPacketSize($config->get('max_packet_size'));

        $this->deviceReset(10000);
        $this->displayOff();
        $this->sleepModeOff();

        // The extended registers below only accept writes while Command 2 is unlocked.
        $this->commandSetEnable();

        $this->setMADControl($config->get('mad_ctrl'));
        $this->setPixelFormat($config->get('color_mode'));
        $this->setDisplayInversionControl($config->get('inversion_ctrl'));
        $this->setDisplayFunctionControl($config->get('display_fn_ctrl'));
        $this->setDisplayOutputCtrlAdjust($config->get('output_adjust'));
        $this->setPowerControl2($config->get('power_control_2'));
        $this->setPowerControl3($config->get('power_control_3'));
        $this->setVComControl($config->get('v_com_ctrl'));
        $this->setColorControl($config->get('gamma_positive'), $config->get('gamma_negative'));

        $this->commandSetDisable();

        $this->setDisplayInversion($config->get('invert_display'));
        $this->setNormalDisplayMode(true);
        $this->displayOn();
    }

    protected function setColorControl(
        ST7796GammaPositive $gamma_positive,
        ST7796GammaNegative $gamma_negative
    ): void {
        $this->setGammaPositive($gamma_positive);
        $this->setGammaNegative($gamma_negative);
    }
}
