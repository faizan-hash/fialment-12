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
        Schema::table('feedback', function (Blueprint $table) {
            // Check if the column doesn't exist before adding it
            if (!Schema::hasColumn('feedback', 'is_positive')) {
                $table->boolean('is_positive')->default(true)->after('comments');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            if (Schema::hasColumn('feedback', 'is_positive')) {
                $table->dropColumn('is_positive');
            }
        });
    }
};
