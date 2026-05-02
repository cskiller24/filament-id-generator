<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $initiatedByModel = config('filament-id-generator.initiated_by_model', 'App\\Models\\User');
        $usersTable = (new $initiatedByModel)->getTable();

        Schema::create('id_generation_batches', function (Blueprint $table) use ($usersTable): void {
            $table->id();
            $table->foreignId('id_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('initiated_by')->nullable()->constrained($usersTable)->nullOnDelete();
            $table->string('status');
            $table->unsignedInteger('total_count')->default(0);
            $table->unsignedInteger('pending_count')->default(0);
            $table->unsignedInteger('processing_count')->default(0);
            $table->unsignedInteger('completed_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->uuid('laravel_batch_id')->nullable();
            $table->string('archive_disk')->nullable();
            $table->string('archive_path')->nullable();
            $table->text('failure_summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('id_generation_batches');
    }
};
