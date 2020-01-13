<?php

return [
    'cache' => [
        'driver'      => 'redis',
        'options'     => [
            'database' => [
                'cluster' => false,
                'default' => [
                    'host'     => '127.0.0.1',
                    'port'     => 6379,
                    'database' => 0,
                ],
            ]
        ],
        'ttl'         => 900,
        'allow_empty' => true
    ]
];
