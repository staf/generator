<?php

use Staf\Generator\Builder;

require __DIR__ . '/../vendor/autoload.php';

/**
 * Setup the builder class, providing the absolute paths to the three working directories.
 *
 * source_path is where the source files are located.
 * target_path is where the static site should be placed.
 * cache_path is the working directory used by the Blade compiler.
 */
$builder = new Builder([
    'source_path' => realpath(__DIR__ . '/source'),
    'target_path' => realpath(__DIR__ . '/build'),
    'cache_path'  => realpath(__DIR__ . '/cache'),
]);

/**
 * Some data that we will be passing to the info pages.
 */
$infoPages = [
    ['url' => '/info', 'name' => 'Information'],
    ['url' => '/info/history', 'name' => 'History'],
];

$posts = require __DIR__ . '/posts.php';

/**
 * Build the static site based on a definition object.
 * Documentation on how this works is coming soon.
 */
$builder->build([
    '/'       => [
        'entry' => 'index',
        'files' => ['favicon.ico'],
        'data' => ['posts' => $posts]
    ],
    'contact' => 'contact',
    'info'    => [
        'entry'    => 'info.index',
        'data' => [
            'infoPages' => $infoPages
        ],
        'children' => [
            'history' => [
                'entry' => 'info.history',
                'data' => [
                    'infoPages' => $infoPages
                ],
            ],
        ],
    ],
    'img'     => [
        'entry' => false,
        'files' => [
            'bird.jpg',
        ],
    ],
    'css'     => [
        'entry' => false,
        'files' => [
            [
                'source' => realpath(__DIR__ . '/../assets/style.css'),
                'name'   => 'style.css',
            ],
        ],
    ],
]);
