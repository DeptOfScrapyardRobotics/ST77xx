<?php

return [
    'default_config' => 'spi',
    'configs' => [
        'spi' => [
            'driver' => 'none',
            'device' => '',
            'chip_select' => 0,
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