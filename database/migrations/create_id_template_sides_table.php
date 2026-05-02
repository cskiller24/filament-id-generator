<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('id_template_sides', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_template_id')->constrained()->cascadeOnDelete();
            $table->string('side');
            $table->string('source_disk');
            $table->string('source_path');
            $table->string('source_mime_type');
            $table->string('preview_disk');
            $table->string('preview_path');
            $table->unsignedTinyInteger('page_number')->nullable();
            $table->unsignedInteger('canvas_width');
            $table->unsignedInteger('canvas_height');
            $table->timestamps();

            $table->unique(['id_template_id', 'side']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('id_template_sides');
    }
};
