<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->text('picture_path')->nullable();
            $table->integer('price')->nullable();
            $table->string('size')->nullable();
            $table->double('bust')->nullable();
            $table->double('waist')->nullable();
            $table->double('hips')->nullable();
            $table->double('length')->nullable();
            $table->string('suitable_for_body_shape')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
