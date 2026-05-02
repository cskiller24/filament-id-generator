# Filament ID Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cskiller/filament-id-generator.svg?style=flat-square)](https://packagist.org/packages/cskiller/filament-id-generator)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cskiller/filament-id-generator/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cskiller/filament-id-generator/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cskiller/filament-id-generator/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cskiller/filament-id-generator/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/cskiller/filament-id-generator.svg?style=flat-square)](https://packagist.org/packages/cskiller/filament-id-generator)

A Filament 5 plugin for bulk ID card generation with a visual canvas-based template editor. Design front and back card layouts in the browser, map fields to any Elo    quent model, and generate hundreds of ID cards (PNG + PDF) in the background via Laravel queues.

## Features

- **Visual template editor** — drag-and-drop canvas (Konva.js) to position text and image fields on front/back sides
- **Any data source** — bring your own Eloquent model (students, employees, members) through a typed adapter interface
- **Queue-based batch rendering** — renders each card in a separate queued job; a final job zips all outputs when done
- **High-resolution output** — Intervention Image + Imagick at configurable DPI; front + back composited into one PDF per card
- **PDF template support** — upload a PDF template and it is automatically rasterized to a preview image
- **Fully headless** — disable either Filament resource and drive generation through the Action classes directly

---

## Table of Contents

1. [Requirements](#requirements)
2. [Installation](#installation)
3. [Quick Start](#quick-start)
4. [Creating an Adapter](#creating-an-adapter)
5. [Registering the Plugin](#registering-the-plugin)
6. [Configuration Reference](#configuration-reference)
7. [Plugin Fluent API](#plugin-fluent-api)
8. [Storage Disks](#storage-disks)
9. [How Templates Work](#how-templates-work)
10. [How Batch Generation Works](#how-batch-generation-works)
11. [Rendering Pipeline](#rendering-pipeline)
12. [Editor Routes](#editor-routes)
13. [Advanced Usage](#advanced-usage)
14. [Testing](#testing)
15. [Contributing](#contributing)
16. [Credits](#credits)
17. [License](#license)

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | ^8.3 |
| Laravel | ^13.0 |
| Filament | ^5.0 |
| PHP Imagick extension | any |
| Ghostscript | any (only for PDF template uploads) |

> **Imagick** must be enabled in your `php.ini`. **Ghostscript** is only required if you plan to upload PDF files as template backgrounds.

---

## Installation

### 1. Require the package

```bash
composer require cskiller/filament-id-generator
```

### 2. Run the install command

The package ships an interactive installer that publishes the config, migrations, and optionally runs them:

```bash
php artisan filament-id-generator:install
```

Or do it manually:

```bash
php artisan vendor:publish --tag="filament-id-generator-config"
php artisan vendor:publish --tag="filament-id-generator-migrations"
php artisan migrate
```

### 3. Configure the queue

Batch rendering uses Laravel's job batching feature. Make sure `QUEUE_CONNECTION` in `.env` is **not** `sync` for production use, and that the required tables exist:

```bash
php artisan queue:batches-table
php artisan queue:table   # only if using the database queue driver
php artisan migrate
```

---

## Quick Start

**1.** Create an adapter for the model you want to generate IDs from (see [Creating an Adapter](#creating-an-adapter)).

**2.** Register the plugin in your Filament panel provider:

```php
// app/Providers/Filament/AdminPanelProvider.php

use App\Support\Adapters\UserIdDataSourceAdapter;
use Cskiller\FilamentIdGenerator\FilamentIdGeneratorPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... your existing panel config
        ->plugin(
            FilamentIdGeneratorPlugin::make()
                ->adapters([
                    'user' => UserIdDataSourceAdapter::class,
                ])
        );
}
```

**3.** Log in to your Filament panel. You will find **ID Templates** and **ID Generation Batches** under the *ID Generator* navigation group.

**4.** Create a template, upload front/back card images, open the editor to position fields, then hit **Generate** on the template edit page.

---

## Creating an Adapter

An adapter connects the plugin to your Eloquent data source (e.g. `User`, `Student`, `Employee`). Implement the `IdDataSourceAdapter` contract:

```php
<?php

namespace App\Support\Adapters;

use App\Models\User;
use Cskiller\FilamentIdGenerator\Contracts\IdDataSourceAdapter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserIdDataSourceAdapter implements IdDataSourceAdapter
{
    public function modelClass(): string
    {
        return User::class;
    }

    public function fields(): array
    {
        return [
            'full_name'   => ['label' => 'Full Name',    'type' => 'text'],
            'employee_id' => ['label' => 'Employee ID',  'type' => 'text'],
            'department'  => ['label' => 'Department',   'type' => 'text'],
            'photo_url'   => ['label' => 'Photo',        'type' => 'image'],
        ];
    }

    public function sampleValues(): array
    {
        return [
            'full_name'   => 'Jane Doe',
            'employee_id' => 'EMP-0042',
            'department'  => 'Engineering',
            'photo_url'   => null,
        ];
    }

    public function query(): Builder
    {
        return User::query()->orderBy('name');
    }

    public function resolve(Model $record, string $mappingKey): mixed
    {
        /** @var User $record */
        return match ($mappingKey) {
            'full_name'   => $record->name,
            'employee_id' => $record->employee_id,
            'department'  => $record->department->name,
            'photo_url'   => $record->photo_url,
            default       => null,
        };
    }

    public function label(Model $record): string
    {
        /** @var User $record */
        return "{$record->name} ({$record->employee_id})";
    }
}
```

### Contract reference

| Method | Return type | Purpose |
|---|---|---|
| `modelClass()` | `string` | FQCN of the source Eloquent model |
| `fields()` | `array<string, array{label: string, type: string}>` | Available mapping keys with display metadata |
| `sampleValues()` | `array<string, mixed>` | Placeholder data for the canvas preview |
| `query()` | `Builder` | Scope used for batch record selection |
| `resolve(Model, string)` | `mixed` | Return the value for `$mappingKey` on `$record` at render time |
| `label(Model)` | `string` | Display label for a record in the UI |

---

## Registering the Plugin

Register the plugin inside your panel provider's `panel()` method. Pass each adapter keyed by a short `target_type` string — this key is stored on templates to identify which adapter to use at render time.

```php
->plugin(
    FilamentIdGeneratorPlugin::make()
        ->adapters([
            'user'     => UserIdDataSourceAdapter::class,
            'employee' => EmployeeIdDataSourceAdapter::class,
        ])
)
```

Multiple panels can each register different adapters independently.

---

## Configuration Reference

After publishing, the config file is at `config/filament-id-generator.php`.

```php
return [

    /*
     * Adapters registered via the plugin fluent API are merged here at boot.
     * You can also register adapters directly in this array.
     */
    'adapters' => [],

    /*
     * The fully-qualified class name of the model that initiates batches.
     * Used for the initiated_by relationship on IdGenerationBatch.
     * Defaults to your application User model.
     */
    'initiated_by_model' => 'App\\Models\\User',

    /*
     * Named disk for storing uploaded template assets (card backgrounds, etc.).
     * The plugin auto-registers this disk (local driver pointing to storage/app).
     * Override to point at S3 or any other disk defined in config/filesystems.php.
     */
    'template_disk' => 'filament-id-generator-templates',

    /*
     * Named disk for storing rendered outputs (PNGs, PDFs, ZIP archives).
     */
    'output_disk' => 'filament-id-generator-outputs',

    /*
     * DPI used when rasterizing PDF template pages into preview images.
     * Lower values are faster; higher values produce sharper previews.
     */
    'preview_dpi' => 150,

    /*
     * DPI used when rendering final output PNGs.
     * 300 is standard print quality for credit-card-sized IDs.
     */
    'render_dpi' => 300,

    /*
     * Absolute path to a default TTF font file used for text fields.
     * Individual fields can override this via their properties JSON.
     */
    'default_font' => null,

    /*
     * Absolute path to a bold variant TTF font file.
     */
    'default_bold_font' => null,

];
```

---

## Plugin Fluent API

All methods return `static` for chaining.

```php
FilamentIdGeneratorPlugin::make()
    // Register adapters keyed by target_type string
    ->adapters(['user' => UserIdDataSourceAdapter::class])

    // Show or hide the ID Templates Filament resource (default: true)
    ->templateResource(true)

    // Show or hide the ID Generation Batches Filament resource (default: true)
    ->batchResource(true)

    // Override the navigation group label (default: 'ID Generator')
    ->navigationGroup('HR Tools')
```

---

## Storage Disks

The service provider **automatically registers** two local disks at boot time:

| Disk name | Default root path | Purpose |
|---|---|---|
| `filament-id-generator-templates` | `storage/app/filament-id-generator/templates` | Uploaded template background images |
| `filament-id-generator-outputs` | `storage/app/filament-id-generator/outputs` | Rendered PNGs, PDFs, and ZIP archives |

### Using a cloud disk

Override either key in the config to point to any disk defined in `config/filesystems.php`:

```php
// config/filament-id-generator.php
'template_disk' => 's3',
'output_disk'   => 's3',
```

The plugin uses `Storage::disk(config('filament-id-generator.template_disk'))` throughout, so switching disks requires only a config change.

---

## How Templates Work

### 1. Create a template

A template (`IdTemplate`) holds:
- **Name** — a human-readable label
- **Target type** — the adapter key (e.g. `user`) linking it to a data source
- **Dimensions** — `width_mm` × `height_mm` (physical card size in millimetres)

### 2. Upload side images

Each template has up to two sides (`IdTemplateSide`): `front` and/or `back`.

Upload a PNG, JPG, or **PDF** for each side. PDF pages are automatically rasterized to a preview PNG via `spatie/pdf-to-image` + Ghostscript at `preview_dpi`. The original file is stored on the template disk; the rasterized preview is what the editor and renderer use.

### 3. Open the canvas editor

Click **Open Editor** on the template. The Konva.js editor loads the preview image and any saved fields. You can:

- **Add fields** from the sidebar (populated by your adapter's `fields()`)
- **Drag and resize** each field box on the canvas
- **Configure text properties** — font, size, alignment, color, line height, overflow mode
- **Configure image properties** — fit mode (contain / cover / stretch)
- **Switch sides** to design front and back independently
- **Save layout** — persists field positions, sizes, and properties to `id_template_fields`

### 4. Field types

| Type | Enum | Description |
|---|---|---|
| Text | `IdTemplateFieldType::Text` | Renders a string value using Imagick text rendering |
| Image | `IdTemplateFieldType::Image` | Fetches a URL or file path and composites the image |

---

## How Batch Generation Works

### Starting a batch

In the Filament UI, select records and click **Generate** on the template edit page. Programmatically:

```php
use Cskiller\FilamentIdGenerator\Actions\StartIdGenerationBatchAction;

$batch = app(StartIdGenerationBatchAction::class)->handle(
    template: $template,
    recordIds: [1, 2, 3, 42], // primary keys of source records
    operator: auth()->user(),  // the model initiating the batch
);
```

### Processing lifecycle

```
StartIdGenerationBatchAction::handle()
  │
  ├─ Creates IdGenerationBatch (status: Pending → Processing)
  ├─ Resolves adapter records matching $recordIds
  ├─ Dispatches one RenderGeneratedIdJob per record via Bus::batch()
  │     └─ Each job calls RenderGeneratedIdAction
  │           ├─ RenderTemplateSideAction (front) → PNG
  │           ├─ RenderTemplateSideAction (back)  → PNG (if side exists)
  │           └─ BuildGeneratedPdfAction          → PDF (front + back composited)
  │
  └─ Bus batch finally() → FinalizeIdGenerationBatchJob
        ├─ BuildBatchArchiveAction → ZIP of all PDFs
        └─ Updates batch status to Completed (or Failed)
```

### Status values

`IdGenerationBatch` uses the `IdGenerationStatus` enum:

| Value | Meaning |
|---|---|
| `Pending` | Created, not yet dispatched |
| `Processing` | Jobs are running |
| `Completed` | All jobs done, ZIP archive built |
| `Failed` | One or more jobs failed |

The **ID Generation Batches** Filament resource shows live status, a progress counter (`completed_count / total_count`), and a **Download Archive** action when the batch completes.

---

## Rendering Pipeline

`RenderTemplateSideAction` is the core renderer. For each card side it:

1. Reads the side's preview image from the template disk (full-resolution PNG)
2. Iterates `id_template_fields` ordered by `z_index` (lowest rendered first, highest on top)
3. For **text fields** — creates a transparent layer matching the field box, draws the resolved text using Imagick with the configured font, size, alignment, line height, and overflow mode, then composites the layer onto the background
4. For **image fields** — resolves the value (URL or file path), reads/downloads the image, resizes it using the configured fit mode, composites onto the background
5. Saves the final composite as a PNG to the output disk

`BuildGeneratedPdfAction` then composites front and back PNGs into a DomPDF-generated PDF and saves it to the output disk.

### Font configuration

```php
// config/filament-id-generator.php
'default_font'      => base_path('resources/fonts/OpenSans-Regular.ttf'),
'default_bold_font' => base_path('resources/fonts/OpenSans-Bold.ttf'),
```

Individual template fields can override the font by storing a `font` key in their `properties` JSON column via the editor.

### Text overflow modes (`IdTextOverflowMode`)

| Value | Behaviour |
|---|---|
| `Wrap` | Text wraps within the field box |
| `Clip` | Text is clipped at the field boundary |

### Image fit modes (`IdImageFitMode`)

| Value | Behaviour |
|---|---|
| `Contain` | Fits the image inside the box, preserving aspect ratio (letterboxed) |
| `Cover` | Fills the box, cropping the image if necessary |
| `Stretch` | Stretches the image to exactly fill the box |

---

## Editor Routes

The plugin registers two routes inside your panel's path prefix during `boot()`:

| Route name | Method | URI | Description |
|---|---|---|---|
| `id-templates.editor` | GET | `/{panel-path}/id-templates/{idTemplate}/editor` | Full-page Konva editor |
| `id-templates.layout.save` | POST | `/{panel-path}/api/id-templates/{idTemplate}/layout` | Persist field layout for one side |

Both routes use the `web` + Filament `Authenticate` middleware, so a valid Filament session is required.

Generate editor URLs from anywhere in your application:

```php
route('id-templates.editor', $template)
```

---

## Advanced Usage

### Using actions directly (headless)

Bypass Filament entirely and call actions from your own code or commands:

```php
use Cskiller\FilamentIdGenerator\Actions\StartIdGenerationBatchAction;
use Cskiller\FilamentIdGenerator\Actions\RenderTemplateSideAction;
use Cskiller\FilamentIdGenerator\Actions\BuildGeneratedPdfAction;

// Start a full batch
$batch = app(StartIdGenerationBatchAction::class)->handle($template, $recordIds, $user);

// Render a single side directly (returns the output path string)
$outputPath = app(RenderTemplateSideAction::class)->handle($side, $record);

// Build a PDF for a single generated asset
app(BuildGeneratedPdfAction::class)->handle($generatedIdAsset);
```

### Resolving the adapter registry

The `IdDataSourceRegistry` singleton resolves adapter instances by their key:

```php
use Cskiller\FilamentIdGenerator\Support\IdDataSourceRegistry;

$registry = app(IdDataSourceRegistry::class);

// Get a resolved adapter instance
$adapter = $registry->for('user');

// Get all registered options as key → headline label map
$options = $registry->options(); // ['user' => 'User', ...]
```

### Disabling Filament resources

If you want to manage templates and batches through your own UI, disable either resource:

```php
FilamentIdGeneratorPlugin::make()
    ->templateResource(false)
    ->batchResource(false)
    ->adapters(['user' => UserIdDataSourceAdapter::class])
```

You can still use all Actions and Jobs directly.

### Publishing stubs

```bash
php artisan vendor:publish --tag="filament-id-generator-stubs"
```

---

## Testing

### Test adapter pattern

In your application's test suite, use a lightweight in-memory adapter instead of hitting real data:

```php
// tests/Fixtures/TestIdDataSourceAdapter.php

use Cskiller\FilamentIdGenerator\Contracts\IdDataSourceAdapter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TestIdDataSourceAdapter implements IdDataSourceAdapter
{
    public function modelClass(): string { return MyModel::class; }

    public function fields(): array
    {
        return [
            'name'  => ['label' => 'Name',  'type' => 'text'],
            'photo' => ['label' => 'Photo', 'type' => 'image'],
        ];
    }

    public function sampleValues(): array { return ['name' => 'Test User', 'photo' => null]; }
    public function query(): Builder { return MyModel::query(); }
    public function resolve(Model $record, string $mappingKey): mixed { return $record->{$mappingKey}; }
    public function label(Model $record): string { return $record->name; }
}
```

Register it in your `TestCase::defineEnvironment()`:

```php
protected function defineEnvironment($app): void
{
    $app['config']->set('filament-id-generator.adapters', [
        'test' => TestIdDataSourceAdapter::class,
    ]);
}
```

### Running the plugin's own test suite

```bash
cd packages/filament-id-generator
composer install
vendor/bin/phpunit
```

---

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [cedy cadayong](https://github.com/cskiller)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
