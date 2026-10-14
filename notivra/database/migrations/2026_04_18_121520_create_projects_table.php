<?php

use App\Models\Service;
use App\Models\User;
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
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Service::class)->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('institution')->nullable();
            $table->string('level')->nullable();
            $table->string('field')->nullable();
            $table->string('topic')->nullable();
            $table->text('description')->nullable();
            $table->string('output')->nullable();
            $table->date('deadline');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->boolean('willing_dp')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->enum('status', ['pending', 'processing', 'final'])->default('pending');
            $table->foreignIdFor(User::class)->nullable()->constrained()->restrictOnDelete();
            $table->string('files')->nullable();
            $table->index('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
