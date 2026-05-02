<?php

// config for Cskiller/FilamentIdGenerator
return [

    /*
    |--------------------------------------------------------------------------
    | Adapters
    |--------------------------------------------------------------------------
    |
    | Register your IdDataSourceAdapter implementations keyed by target_type.
    | Example: 'user' => \App\Support\UserIdDataSourceAdapter::class
    |
    */
    'adapters' => [],

    /*
    |--------------------------------------------------------------------------
    | Initiated By Model
    |--------------------------------------------------------------------------
    |
    | The fully-qualified class name of the model that initiates ID generation
    | batches (typically your User model).
    |
    */
    'initiated_by_model' => 'App\\Models\\User',

    /*
    |--------------------------------------------------------------------------
    | Storage Disks
    |--------------------------------------------------------------------------
    |
    | The disk names used for storing ID template assets and generated outputs.
    | Defaults are registered automatically by the plugin service provider.
    |
    */
    'template_disk' => 'filament-id-generator-templates',
    'output_disk' => 'filament-id-generator-outputs',

    /*
    |--------------------------------------------------------------------------
    | Rendering Resolution
    |--------------------------------------------------------------------------
    |
    | DPI used when rasterizing PDF template pages for preview and for final
    | output rendering.
    |
    */
    'preview_dpi' => 150,
    'render_dpi' => 300,

    /*
    |--------------------------------------------------------------------------
    | Default Fonts
    |--------------------------------------------------------------------------
    |
    | Absolute paths to default TTF fonts used when rendering text fields.
    | Set to null if no default font is required (fields must specify one).
    |
    */
    'default_font' => null,
    'default_bold_font' => null,

];
