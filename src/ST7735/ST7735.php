<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\ST7735;

use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Concerns\ST7735Bootstrap;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST7735\Enums\ST7735OpCode;
use DeptOfScrapyardRobotics\Displays\ST77xx\ST77xxFills;
use DeptOfScrapyardRobotics\Displays\ST77xx\Transports\ST77xxDataTransport;
use GeneralPurposeIO\IntegratedCircuits\Bootable;
use Surface\Contracts\Framebuffers\BitDepth;
use Surface\Contracts\Framebuffers\Endianness;
use Surface\Contracts\Framebuffers\FormatSpec;
use Surface\Contracts\Framebuffers\FormatSpecification;
use Surface\Contracts\Framebuffers\PixelFormat;
use Surface\Contracts\Framebuffers\ScanDirection;
use GeneralPurposeIO\Contracts\IntegratedCircuits\DisplayPanel;

class ST7735 extends Bootable implements DisplayPanel, FormatSpecification
{
    use ST7735Bootstrap;
    use ST77xxFills;

    protected FormatSpec $format_spec;

    public function __construct(
        protected readonly ST77xxDataTransport $transport,
        protected ST7735Configuration $props,
        bool $boot_now = false,
    ) {
        $this->format_spec = $this->generateFormatSpec();

        parent::__construct($boot_now);
    }

    public function width(): int
    {
        return $this->props->get('width');
    }

    public function height(): int
    {
        return $this->props->get('height');
    }

    public function transmit(int $origin_x, int $origin_y, array $raw_data, ?int $frame_width = null, ?int $frame_height = null): void
    {
        $this->setAddressWindow(
            $origin_x,
            $origin_y,
            $frame_width ?? $this->width(),
            $frame_height ?? $this->height()
        );

        $this->transport()->command(ST7735OpCode::WRITE_MEMORY_START->value);
        $this->transport()->data($raw_data);
    }

    public function transport(): ST77xxDataTransport
    {
        return $this->transport;
    }

    /** Release DC and RST on SPI; the bus connection belongs to its driver and stays open. */
    public function close(): void
    {
        $this->transport->close();
    }

    public function config(): ST7735Configuration
    {
        return $this->props;
    }

    /** How the panel wants its bytes packed, for the addressing mode it is in. Set at boot. */
    public function formatSpec(): FormatSpec
    {
        return $this->format_spec;
    }

    public function setFormatSpec(FormatSpec $format_spec): void
    {
        $this->format_spec = $format_spec;
    }

    public function generateFormatSpec(): FormatSpec
    {
        /** @var ST7789ColorMode $color_mode */
        $color_mode = $this->config()->get('color_mode');
        return new FormatSpec(
            PixelFormat::ROW_MAJOR,
            BitDepth::from($color_mode->bitsPerPixel()),
            ScanDirection::TOP_TO_BOTTOM,
            endianness: Endianness::MSB,
        );
    }
}