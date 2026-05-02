<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('id_template_fields', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_template_side_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('type');
            $table->string('mapping_key')->nullable();
            $table->unsignedInteger('x');
            $table->unsignedInteger('y');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->unsignedInteger('z_index')->default(0);
            $table->boolean('is_required')->default(false);
            $table->text('default_value')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('id_template_fields');
    }
};
