<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('custom_buttons')) {
            return;
        }

        Schema::table('custom_buttons', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_buttons', 'server_id')) {
                $table->unsignedInteger('server_id')->nullable()->after('id');
                $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('custom_buttons', function (Blueprint $table) {
            $table->dropForeign(['server_id']);
            $table->dropColumn('server_id');
        });
    }
};
