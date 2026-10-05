<?php

return [
    'default_config' => 'spi',
    'configs' => [
        'spi' => [
            'driver' => 'none',
            'device' => '',
            'chip_select' => 0,
            'mode' => 0,
            'speed' => 10_000_000,  // Hz, this chip select's clock
            'width' => null,        // null keeps the controller's default size
            'height' => null,
            'mad_ctrl' => [],       // named MADControl flags, e.g. ['pixel_direction_vertical' => true]
            'x_offset' => null,     // null keeps the controller's default offsets
            'y_offset' => null,
            'invert_display' => null, // null keeps the controller's default
            'dc' => [
                'driver' => 'none',
                'device' => '',
                'pin' => 0,
            ],
            'rst' => [
                'driver' => 'none',
                'device' => '',
                'pin' => 1,
            ],
        ],
    ],
];
