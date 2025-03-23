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
        // Only run this migration if the feedback table exists
        if (Schema::hasTable('feedback')) {
            Schema::table('feedback', function (Blueprint $table) {
                // Rename receiver_id to recipient_id
                if (Schema::hasColumn('feedback', 'receiver_id')) {
                    $table->renameColumn('receiver_id', 'recipient_id');
                }
                
                // Add is_positive column if it doesn't exist
                if (!Schema::hasColumn('feedback', 'is_positive')) {
                    $table->boolean('is_positive')->default(true)->after('comments');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only run this migration if the feedback table exists
        if (Schema::hasTable('feedback')) {
            Schema::table('feedback', function (Blueprint $table) {
                // Rename recipient_id back to receiver_id
                if (Schema::hasColumn('feedback', 'recipient_id')) {
                    $table->renameColumn('recipient_id', 'receiver_id');
                }
                
                // Drop is_positive column if it exists
                if (Schema::hasColumn('feedback', 'is_positive')) {
                    $table->dropColumn('is_positive');
                }
            });
        }
    }
}; 