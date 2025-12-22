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
        // Rename column from path to storage_uri
        Schema::table('snapshots', function (Blueprint $table) {
            $table->renameColumn('path', 'storage_uri');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename column back
        Schema::table('snapshots', function (Blueprint $table) {
            $table->renameColumn('storage_uri', 'path');
        });
    }
};
