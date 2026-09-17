<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7796;

use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Breakouts\ST7796DisplayFunctionControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Breakouts\ST7796DisplayInversionControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Breakouts\ST7796DisplayOutputCtrlAdjust;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Breakouts\ST7796GammaNegative;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Breakouts\ST7796GammaPositive;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Breakouts\ST7796MADControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Breakouts\ST7796PowerControl2;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Breakouts\ST7796PowerControl3;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Breakouts\ST7796VCOMControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7796\Enums\ST7796ColorMode;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;

class ST7796Configuration
{
    protected bool $display_on = false;

    protected bool $sleep_mode_on = false;

    public function __construct(
        protected int $width = 480,
        protected int $height = 320,
        protected int $max_packet_size = 4092,
        protected int $x_offset = 0,
        protected int $y_offset = 0,
        protected ?ST7796MADControl $mad_ctrl = null,
        protected ?ST7796ColorMode $color_mode = null,
        protected ?ST7796DisplayInversionControl $inversion_ctrl = null,
        protected ?ST7796DisplayFunctionControl $display_fn_ctrl = null,
        protected ?ST7796DisplayOutputCtrlAdjust $output_adjust = null,
        protected ?ST7796PowerControl2 $power_control_2 = null,
        protected ?ST7796PowerControl3 $power_control_3 = null,
        protected ?ST7796VCOMControl $v_com_ctrl = null,
        protected ?ST7796GammaPositive $gamma_positive = null,
        protected ?ST7796GammaNegative $gamma_negative = null,
        protected bool $invert_display = false,
    ) {
        $this->color_mode ??= ST7796ColorMode::COLOR16;
        $this->mad_ctrl ??= new ST7796MADControl(
            false,
            false,
            true,
            false,
            true,
            false,
        );
        $this->inversion_ctrl ??= new ST7796DisplayInversionControl;
        $this->display_fn_ctrl ??= new ST7796DisplayFunctionControl;
        $this->output_adjust ??= new ST7796DisplayOutputCtrlAdjust;
        $this->power_control_2 ??= new ST7796PowerControl2;
        $this->power_control_3 ??= new ST7796PowerControl3;
        $this->v_com_ctrl ??= new ST7796VCOMControl;
        $this->gamma_positive ??= new ST7796GammaPositive;
        $this->gamma_negative ??= new ST7796GammaNegative;
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