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
        Schema::create('assignment_verifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assignment_group_member_id')
                ->unique()
                ->constrained('assignment_group_members')
                ->cascadeOnDelete();

            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
                'expired',
                'invalid',
            ])->default('pending');

            // Progres jawaban individual.
            $table->json('answers')->nullable();

            // Simpan hash token, bukan token mentah.
            $table->char('resume_token_hash', 64)
                ->nullable()
                ->unique();

            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('token_revoked_at')->nullable();

            $table->string('start_token_hash', 64)->nullable()->unique();
            $table->text('start_token_encrypted')->nullable();
            $table->timestamp('start_token_used_at')->nullable();

            $table->unsignedSmallInteger('attempt_count')->default(0);

            // Bukti pendukung, bukan bukti identitas mutlak.
            $table->string('last_ip_address', 45)->nullable();
            $table->text('last_user_agent')->nullable();
            $table->char('device_token_hash', 64)->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('quiz_expires_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'token_expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_verifications');
    }
};
