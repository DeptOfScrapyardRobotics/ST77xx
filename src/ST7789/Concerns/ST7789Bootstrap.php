<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Concerns;

use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789GammaNegative;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789GammaPositive;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;

trait ST7789Bootstrap
{
    use ST7789API;

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
            'porch_ctrl' => $this->setPorchControl($value),
            'gate_ctrl' => $this->setGateControl($value),
            'v_com_ctrl' => $this->setVComControl($value),
            'lcm_ctrl' => $this->setLcmControl($value),
            'vdv_vrh_enable' => $this->setVdvVrhEnable($value),
            'vrh' => $this->setVrh($value),
            'vdv' => $this->setVdv($value),
            'frame_rate_ctrl' => $this->setFrameRateControlNormal($value),
            'power_control_1' => $this->setPowerControl1($value),
            'gamma_positive' => $this->setGammaPositive($value),
            'gamma_negative' => $this->setGammaNegative($value),
            default => throw ST77xxException::invalidProperty($name, static::class),
        };
    }

    protected function _boot(): void
    {
        $config = $this->config();
        $this->transport()->maxPacketSize($config->get('max_packet_size'));

        $this->deviceReset(3000);
        $this->displayOff();
        $this->sleepModeOff();

        $this->setMADControl($config->get('mad_ctrl'));
        $this->setPixelFormat($config->get('color_mode'));
        $this->setPorchControl($config->get('porch_ctrl'));
        $this->setGateControl($config->get('gate_ctrl'));
        $this->setVComControl($config->get('v_com_ctrl'));
        $this->setLcmControl($config->get('lcm_ctrl'));
        $this->setVdvVrhEnable($config->get('vdv_vrh_enable'));
        $this->setVrh($config->get('vrh'));
        $this->setVdv($config->get('vdv'));
        $this->setFrameRateControlNormal($config->get('frame_rate_ctrl'));
        $this->setPowerControl1($config->get('power_control_1'));
        $this->setColorControl($config->get('gamma_positive'), $config->get('gamma_negative'));

        $this->setDisplayInversion($config->get('invert_display'));
        $this->setNormalDisplayMode(true);
        $this->displayOn();
    }

    protected function setColorControl(
        ST7789GammaPositive $gamma_positive,
        ST7789GammaNegative $gamma_negative
    ): void {
        $this->setGammaPositive($gamma_positive);
        $this->setGammaNegative($gamma_negative);
    }
}
