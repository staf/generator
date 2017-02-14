<?php

return [

    // Set the directory where the view files for the static site are located.
    'source_path' => '',

    // Set the target directory we are going to build the site in.
    'target_path' => '',

    // Set the cache directory used by the Blade compiler
    'cache_path'  => storage_path('framework/views'),

    /**
     * Basic way of defining static site definitions. This is not the best and most
     * convenient way however and should be replaced by a better script that more
     * suit your own needs.
     */
    'definitions' => [

        // Default static site definition
        'default' => [],

    ],

];
