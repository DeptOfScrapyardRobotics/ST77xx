<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7735;

use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735GammaNegative;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735GammaPositive;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735IdleModeFrameRateControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735MADControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735NormalFrameRateControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735PartialModeFrameRateControl;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735PowerControl1;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735PowerControl2;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735PowerControl3;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735PowerControl4;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735PowerControl5;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Breakouts\ST7735VCOMControl1;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Enums\ST7735ColorMode;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;

class ST7735Configuration
{
    protected bool $display_on = false;

    protected bool $sleep_mode_on = false;

    public function __construct(
        protected int $width = 128,
        protected int $height = 128,
        protected int $max_packet_size = 2048,
        protected int $x_offset = 0,
        protected int $y_offset = 0,
        protected bool $invert_display = false,
        protected ?ST7735NormalFrameRateControl $nfc = null,
        protected ?ST7735IdleModeFrameRateControl $ifc = null,
        protected ?ST7735PartialModeFrameRateControl $pfc = null,
        protected ?ST7735PowerControl1 $power_control_1 = null,
        protected ?ST7735PowerControl2 $power_control_2 = null,
        protected ?ST7735PowerControl3 $power_control_3 = null,
        protected ?ST7735PowerControl4 $power_control_4 = null,
        protected ?ST7735PowerControl5 $power_control_5 = null,
        protected ?ST7735VCOMControl1 $v_com_ctrl = null,
        protected ?ST7735MADControl $mad_ctrl = null,
        protected ?ST7735ColorMode $color_mode = null,
        protected ?ST7735GammaPositive $gamma_positive = null,
        protected ?ST7735GammaNegative $gamma_negative = null,
        protected int $inversion_control = 0x07,
    ) {
        $this->color_mode ??= ST7735ColorMode::COLOR16;
        $this->nfc ??= ST7735NormalFrameRateControl::fromBytes();
        $this->ifc ??= ST7735IdleModeFrameRateControl::fromBytes();
        $this->pfc ??= ST7735PartialModeFrameRateControl::fromBytes();

        $this->power_control_1 ??= new ST7735PowerControl1;
        $this->power_control_2 ??= new ST7735PowerControl2;
        $this->power_control_3 ??= new ST7735PowerControl3;
        $this->power_control_4 ??= new ST7735PowerControl4;
        $this->power_control_5 ??= new ST7735PowerControl5;
        $this->v_com_ctrl ??= new ST7735VCOMControl1;

        $this->mad_ctrl ??= new ST7735MADControl;

        $this->gamma_positive ??= new ST7735GammaPositive;
        $this->gamma_negative ??= new ST7735GammaNegative;
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