<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Concerns;

use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735GammaNegative;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735GammaPositive;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735IdleModeFrameRateControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735NormalFrameRateControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735PartialModeFrameRateControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735PowerControl1;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735PowerControl2;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735PowerControl3;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735PowerControl4;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735PowerControl5;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735VCOMControl1;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;

trait ST7735Bootstrap
{
    use ST7735API;

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
            'nfc' => $this->setFrameRateControlNormal($value),
            'ifc' => $this->setFrameRateControlIdle($value),
            'pfc' => $this->setFrameRateControlPartial($value),
            'inversion_control' => $this->setInversionControl($value),
            'power_control_1' => $this->setPowerControl1($value),
            'power_control_2' => $this->setPowerControl2($value),
            'power_control_3' => $this->setPowerControl3($value),
            'power_control_4' => $this->setPowerControl4($value),
            'power_control_5' => $this->setPowerControl5($value),
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

        // The controller ignores commands for up to 120 ms after reset exits.
        // The old 10 ms delay made cold boots dependent on the panel's prior
        // power state, which is why a warm panel could work while a fresh boot
        // remained completely inert.
        $this->deviceReset(150000);
        $this->softwareReset();
        usleep(150000);
        $this->sleepModeOff();

        $this->setFrameRateControl($config->get('nfc'), $config->get('ifc'), $config->get('pfc'));
        $this->setInversionControl($config->get('inversion_control'));
        $this->setPowerControl(
            $config->get('power_control_1'),
            $config->get('power_control_2'),
            $config->get('power_control_3'),
            $config->get('power_control_4'),
            $config->get('power_control_5'),
            $config->get('v_com_ctrl'),
        );

        $this->setDisplayInversion($config->get('invert_display'));
        $this->setMADControl($config->get('mad_ctrl'));
        $this->setPixelFormat($config->get('color_mode'));
        $this->setColorControl($config->get('gamma_positive'), $config->get('gamma_negative'));
        $this->setNormalDisplayMode(true);
        usleep(10000);
        $this->displayOn();
        usleep(100000);
    }

    protected function setFrameRateControl(
        ST7735NormalFrameRateControl $nfc,
        ST7735IdleModeFrameRateControl $ifc,
        ST7735PartialModeFrameRateControl $pfc
    ): void {
        $this->setFrameRateControlNormal($nfc);
        $this->setFrameRateControlIdle($ifc);
        $this->setFrameRateControlPartial($pfc);
    }

    protected function setPowerControl(
        ST7735PowerControl1 $pwr_ctrl1,
        ST7735PowerControl2 $pwr_ctrl2,
        ST7735PowerControl3 $pwr_ctrl3,
        ST7735PowerControl4 $pwr_ctrl4,
        ST7735PowerControl5 $pwr_ctrl5,
        ST7735VCOMControl1 $v_com_ctrl,
    ): void {
        $this->setPowerControl1($pwr_ctrl1);
        $this->setPowerControl2($pwr_ctrl2);
        $this->setPowerControl3($pwr_ctrl3);
        $this->setPowerControl4($pwr_ctrl4);
        $this->setPowerControl5($pwr_ctrl5);
        $this->setVComControl($v_com_ctrl);
    }

    protected function setColorControl(
        ST7735GammaPositive $gamma_positive,
        ST7735GammaNegative $gamma_negative
    ): void {
        $this->setGammaPositive($gamma_positive);
        $this->setGammaNegative($gamma_negative);
    }
}
