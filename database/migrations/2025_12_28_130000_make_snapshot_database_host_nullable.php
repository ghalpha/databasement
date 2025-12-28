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
        Schema::table('snapshots', function (Blueprint $table) {
            $table->string('database_host')->nullable()->change();
            $table->integer('database_port')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('snapshots', function (Blueprint $table) {
            $table->string('database_host')->nullable(false)->change();
            $table->integer('database_port')->nullable(false)->change();
        });
    }
};
