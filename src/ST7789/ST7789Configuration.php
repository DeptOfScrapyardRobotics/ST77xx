<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7789;

use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789FrameRateControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789GammaNegative;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789GammaPositive;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789GateControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789LCMControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789MADControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789PorchControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789PowerControl1;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789VCOMControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789VDVSet;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789VDVVRHEnable;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Breakouts\ST7789VRHSet;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7789\Enums\ST7789ColorMode;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;

class ST7789Configuration
{
    protected bool $display_on = false;

    protected bool $sleep_mode_on = false;

    public function __construct(
        protected int $width = 240,
        protected int $height = 240,
        protected int $max_packet_size = 2048,
        protected int $x_offset = 0,
        protected int $y_offset = 0,
        protected ?ST7789MADControl $mad_ctrl = null,
        protected ?ST7789ColorMode $color_mode = null,
        protected ?ST7789PorchControl $porch_ctrl = null,
        protected ?ST7789GateControl $gate_ctrl = null,
        protected ?ST7789VCOMControl $v_com_ctrl = null,
        protected ?ST7789LCMControl $lcm_ctrl = null,
        protected ?ST7789VDVVRHEnable $vdv_vrh_enable = null,
        protected ?ST7789VRHSet $vrh = null,
        protected ?ST7789VDVSet $vdv = null,
        protected ?ST7789FrameRateControl $frame_rate_ctrl = null,
        protected ?ST7789PowerControl1 $power_control_1 = null,
        protected ?ST7789GammaPositive $gamma_positive = null,
        protected ?ST7789GammaNegative $gamma_negative = null,
        protected bool $invert_display = true,
    ) {
        $this->color_mode ??= ST7789ColorMode::COLOR16;
        $this->porch_ctrl ??= new ST7789PorchControl;
        $this->gate_ctrl ??= new ST7789GateControl;
        $this->v_com_ctrl ??= new ST7789VCOMControl;
        $this->lcm_ctrl ??= new ST7789LCMControl;
        $this->vdv_vrh_enable ??= new ST7789VDVVRHEnable;
        $this->vrh ??= new ST7789VRHSet;
        $this->vdv ??= new ST7789VDVSet;
        $this->frame_rate_ctrl ??= new ST7789FrameRateControl;
        $this->power_control_1 ??= new ST7789PowerControl1;
        $this->mad_ctrl ??= new ST7789MADControl(
            false,
            false,
            false,
            false,
            false,
            false,
        );
        $this->gamma_positive ??= new ST7789GammaPositive;
        $this->gamma_negative ??= new ST7789GammaNegative;
    }

    public function get(string $var): mixed
    {
        if(isset($this->$var))
        {
            return $this->$var;
        }

        throw ST77xxException::invalidProperty($var, static::class);
    }

    public function set(string $var, mixed $value): void
    {
        if(isset($this->$var))
        {
            $this->$var = $value;

            return;
        }

        throw ST77xxException::invalidProperty($var, static::class);
    }
}