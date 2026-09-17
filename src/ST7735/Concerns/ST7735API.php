<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Concerns;

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
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Enums\ST7735OpCode;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\ST7735Configuration;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxException;
use DeptOfScrapyardRobotics\Displays\ST77xx\Transports\ST77xxDataTransport;
use Surface\Contracts\Framebuffers\FormatSpec;

trait ST7735API
{
    abstract public function config(): ST7735Configuration;
    abstract public function transport(): ST77xxDataTransport;
    abstract public function setFormatSpec(FormatSpec $format_spec): void;
    abstract public function generateFormatSpec(): FormatSpec;

    protected function sendCommand(ST7735OpCode $register, array $command_data = []): int
    {
        return $this->transport()->command($register->value, $command_data);
    }

    protected function deviceReset(int $sleep_us): void
    {
        $this->transport()->reset($sleep_us);
    }

    public function softwareReset(): void
    {
        $this->sendCommand(ST7735OpCode::SOFTWARE_RESET);
    }

    public function displayOn(): void
    {
        $this->sendCommand(ST7735OpCode::TOGGLE_DISPLAY_ON);
        $this->config()->set('display_on', true);
    }

    public function displayOff(): void
    {
        $this->sendCommand(ST7735OpCode::TOGGLE_DISPLAY_OFF);
        $this->config()->set('display_on', false);
    }

    public function sleepModeOn(): void
    {
        $this->sendCommand(ST7735OpCode::ENTER_SLEEP_MODE);
        $this->config()->set('sleep_mode_on', true);
    }

    /** The controller ignores commands for 120 ms after leaving sleep. */
    public function sleepModeOff(): void
    {
        $this->sendCommand(ST7735OpCode::EXIT_SLEEP_MODE);
        usleep(120000);
        $this->config()->set('sleep_mode_on', false);
    }

    public function setFrameRateControlNormal(ST7735NormalFrameRateControl $control): void
    {
        $this->sendCommand(ST7735OpCode::FRAME_RATE_CONTROL_NORMAL, $control->toBytes());
        $this->config()->set('nfc', $control);
    }

    public function setFrameRateControlIdle(ST7735IdleModeFrameRateControl $control): void
    {
        $this->sendCommand(ST7735OpCode::FRAME_RATE_CONTROL_IDLE, $control->toBytes());
        $this->config()->set('ifc', $control);
    }

    public function setFrameRateControlPartial(ST7735PartialModeFrameRateControl $control): void
    {
        $this->sendCommand(ST7735OpCode::FRAME_RATE_CONTROL_PARTIAL, $control->toBytes());
        $this->config()->set('pfc', $control);
    }

    /** INVCTR (0xB4): the NLA/NLB/NLC inversion bits, 0x07 = column inversion in every mode. */
    public function setInversionControl(int $value): void
    {
        if (($value < 0) || ($value > 0x07)) {
            throw ST77xxException::invalidRegisterValue('inversion_control', $value, 0, 0x07);
        }

        $this->sendCommand(ST7735OpCode::INVERSION_CONTROL, [$value]);
        $this->config()->set('inversion_control', $value);
    }

    public function setPowerControl1(ST7735PowerControl1 $register): void
    {
        $this->sendCommand(ST7735OpCode::POWER_CONTROL_1, $register->toBytes());
        $this->config()->set('power_control_1', $register);
    }

    public function setPowerControl2(ST7735PowerControl2 $register): void
    {
        $this->sendCommand(ST7735OpCode::POWER_CONTROL_2, [$register->toByte()]);
        $this->config()->set('power_control_2', $register);
    }

    public function setPowerControl3(ST7735PowerControl3 $register): void
    {
        $this->sendCommand(ST7735OpCode::POWER_CONTROL_3, $register->toBytes());
        $this->config()->set('power_control_3', $register);
    }

    public function setPowerControl4(ST7735PowerControl4 $register): void
    {
        $this->sendCommand(ST7735OpCode::POWER_CONTROL_4, $register->toBytes());
        $this->config()->set('power_control_4', $register);
    }

    public function setPowerControl5(ST7735PowerControl5 $register): void
    {
        $this->sendCommand(ST7735OpCode::POWER_CONTROL_5, $register->toBytes());
        $this->config()->set('power_control_5', $register);
    }

    public function setVComControl(ST7735VCOMControl1 $register): void
    {
        $this->sendCommand(ST7735OpCode::VCOM_CONTROL_1, [$register->toByte()]);
        $this->config()->set('v_com_ctrl', $register);
    }

    public function displayInversionOn(): void
    {
        $this->sendCommand(ST7735OpCode::DISPLAY_INVERSION_ON);
        $this->config()->set('invert_display', true);
    }

    public function displayInversionOff(): void
    {
        $this->sendCommand(ST7735OpCode::DISPLAY_INVERSION_OFF);
        $this->config()->set('invert_display', false);
    }

    public function displayNormalMode(): void
    {
        $this->sendCommand(ST7735OpCode::NORMAL_MODE_ON);
    }

    public function displayPartialMode(): void
    {
        $this->sendCommand(ST7735OpCode::PARTIAL_MODE_ON);
    }

    public function setMADControl(ST7735MADControl $control): void
    {
        $this->sendCommand(ST7735OpCode::MEMORY_ACCESS_CONTROL, [$control->toByte()]);
        $this->config()->set('mad_ctrl', $control);
    }

    public function setGammaPositive(ST7735GammaPositive $gamma_positive): void
    {
        $this->sendCommand(ST7735OpCode::GAMMA_CORRECTION_POSITIVE, $gamma_positive->toBytes());
        $this->config()->set('gamma_positive', $gamma_positive);
    }

    public function setGammaNegative(ST7735GammaNegative $gamma_negative): void
    {
        $this->sendCommand(ST7735OpCode::GAMMA_CORRECTION_NEGATIVE, $gamma_negative->toBytes());
        $this->config()->set('gamma_negative', $gamma_negative);
    }

    /**
     * @throws ST77xxException
     */
    public function setPixelFormat(ST7735ColorMode|int $color_mode): void
    {
        if (is_int($color_mode)) {
            $color_mode = match ($color_mode) {
                12 => ST7735ColorMode::COLOR12,
                16 => ST7735ColorMode::COLOR16,
                18 => ST7735ColorMode::COLOR18,
                default => throw ST77xxException::invalidColorMode($color_mode),
            };
        }

        $this->sendCommand(ST7735OpCode::SET_PIXEL_FORMAT, [$color_mode->value]);
        $this->config()->set('color_mode', $color_mode);
        $this->setFormatSpec($this->generateFormatSpec());
    }

    /** Column and row window in panel coordinates; the configured offsets are added here. */
    public function setAddressWindow(int $x, int $y, int $width, int $height): void
    {
        $x_start = $x + $this->config()->get('x_offset');
        $x_end = $x_start + $width - 1;
        $y_start = $y + $this->config()->get('y_offset');
        $y_end = $y_start + $height - 1;

        $this->sendCommand(ST7735OpCode::SET_COLUMN_ADDRESS, [
            ($x_start >> 8) & 0xFF, $x_start & 0xFF, ($x_end >> 8) & 0xFF, $x_end & 0xFF,
        ]);
        $this->sendCommand(ST7735OpCode::SET_ROW_ADDRESS, [
            ($y_start >> 8) & 0xFF, $y_start & 0xFF, ($y_end >> 8) & 0xFF, $y_end & 0xFF,
        ]);
    }

    public function setPartialDisplayMode(bool $on): void
    {
        $on ? $this->displayPartialMode() : $this->displayNormalMode();
    }

    public function setNormalDisplayMode(bool $on): void
    {
        $on ? $this->displayNormalMode() : $this->displayPartialMode();
    }

    public function setDisplayInversion(bool $on): void
    {
        $on ? $this->displayInversionOn() : $this->displayInversionOff();
    }

    public function setSleepMode(bool $on): void
    {
        $on ? $this->sleepModeOn() : $this->sleepModeOff();
    }

    public function setDisplay(bool $on): void
    {
        $on ? $this->displayOn() : $this->displayOff();
    }
}
