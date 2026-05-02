<?php

namespace Cskiller\FilamentIdGenerator\Commands;

use Illuminate\Console\Command;

class FilamentIdGeneratorCommand extends Command
{
    public $signature = 'filament-id-generator';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
