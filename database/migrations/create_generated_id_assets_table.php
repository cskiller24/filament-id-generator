<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_id_assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_generation_batch_id')->constrained()->cascadeOnDelete();
            $table->morphs('source');
            $table->string('status');
            $table->string('front_png_disk')->nullable();
            $table->string('front_png_path')->nullable();
            $table->string('back_png_disk')->nullable();
            $table->string('back_png_path')->nullable();
            $table->string('pdf_disk')->nullable();
            $table->string('pdf_path')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(
                ['id_generation_batch_id', 'source_type', 'source_id'],
                'generated_assets_batch_source_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_id_assets');
    }
};
