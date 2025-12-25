    <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('custom_buttons')) {
            Schema::table('custom_buttons', function (Blueprint $table) {
                if (!Schema::hasColumn('custom_buttons', 'feature')) {
                    $table->string('feature')->nullable()->after('is_active');
                }
            });
        }

        if (Schema::hasTable('custom_sidebar_items')) {
            Schema::table('custom_sidebar_items', function (Blueprint $table) {
                if (!Schema::hasColumn('custom_sidebar_items', 'feature')) {
                    $table->string('feature')->nullable()->after('is_active');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('custom_buttons', function (Blueprint $table) {
            $table->dropColumn('feature');
        });

        Schema::table('custom_sidebar_items', function (Blueprint $table) {
            $table->dropColumn('feature');
        });
    }
};
