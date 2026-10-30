<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_quiz_attempts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assignment_participant_id')
                ->unique()
                ->constrained('assignment_participants')
                ->cascadeOnDelete();

            $table->json('question_ids')->nullable();

            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
                'expired',
                'invalid',
            ])->default('pending');

            $table->json('answers')->nullable();

            $table->char('resume_token_hash', 64)
                ->nullable()
                ->unique();

            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('token_revoked_at')->nullable();

            $table->char('device_token_hash', 64)->nullable();
            $table->string('last_ip_address', 45)->nullable();
            $table->text('last_user_agent')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expired_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_quiz_attempts');
    }
};
