<?php

require __DIR__ . '/../vendor/autoload.php';

/**
 * Setup the builder class, providing the absolute paths to the three working directories.
 *
 * source_path is where the source files are located.
 * target_path is where the static site should be placed.
 * cache_path is the working directory used by the Blade compiler.
 */
$builder = new \Staf\Builder([
    'source_path' => realpath(__DIR__ . '/source'),
    'target_path' => realpath(__DIR__ . '/build'),
    'cache_path'  => realpath(__DIR__ . '/cache'),
]);

/**
 * Build the static site based on a definition object.
 * Documentation on how this works is coming soon.
 */
$builder->build([
    '/'       => 'index',
    'contact' => 'contact',
    'info'    => [
        'entry'    => 'info.index',
        'children' => [
            'history' => 'info.history',
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
