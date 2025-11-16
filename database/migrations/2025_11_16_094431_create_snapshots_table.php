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
        Schema::create('snapshots', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Relationships
            $table->foreignUlid('database_server_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('backup_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('volume_id')->constrained()->cascadeOnDelete();

            // File & Storage Information
            $table->string('path'); // Full path to backup file
            $table->unsignedBigInteger('file_size'); // Compressed backup size in bytes
            $table->string('checksum')->nullable(); // SHA256 hash

            // Execution Timing
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();

            // Status & Result
            $table->enum('status', ['pending', 'running', 'completed', 'failed']);
            $table->text('error_message')->nullable();
            $table->text('error_trace')->nullable();

            // Database Context (at time of backup)
            $table->string('database_name');
            $table->string('database_type'); // mysql, postgresql, mariadb
            $table->string('database_host');
            $table->integer('database_port');
            $table->unsignedBigInteger('database_size_bytes')->nullable();

            // Backup Configuration
            $table->string('compression_type')->default('gzip');
            $table->enum('method', ['manual', 'scheduled']);

            // Audit
            $table->foreignUlid('triggered_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('snapshots');
    }
};
