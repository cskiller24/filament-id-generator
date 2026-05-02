<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('id_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('target_type');
            $table->decimal('width_mm', 8, 2)->default(85.60);
            $table->decimal('height_mm', 8, 2)->default(53.98);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('id_templates');
    }
};
