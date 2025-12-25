<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('custom_buttons')) {
            return;
        }

        Schema::create('custom_buttons', function (Blueprint $table) {
            $table->id();
            $table->string('text');
            $table->string('url');
            $table->string('icon')->nullable();
            $table->string('color')->default('primary');
            $table->boolean('new_tab')->default(true);
            $table->integer('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_buttons');
    }
};
