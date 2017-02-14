<?php

require __DIR__ . '/../vendor/autoload.php';

$builder = new \Staf\Builder([
    'cache_path'  => realpath(__DIR__ . '/cache'),
    'source_path' => realpath(__DIR__ . '/source'),
    'target_path' => realpath(__DIR__ . '/build'),
]);

$builder->build([
    '/'       => 'index',
    'contact' => 'contact',
    'info' => [
        'entry' => 'info.index',
        'children' => [
            'history' => 'info.history'
        ]
    ],
    'img' => [
        'entry' => false,
        'files' => [
            'bird.jpg'
        ]
    ]
]);