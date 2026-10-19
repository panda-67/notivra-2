<?php

namespace Database\Factories;

use App\Models\AssignmentGroupMember;
use App\Models\AssignmentVerification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AssignmentVerification>
 */
class AssignmentVerificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'assignment_group_member_id' => AssignmentGroupMember::factory(),
            'status' => 'pending',
            'answers' => [],
            'resume_token_hash' => hash('sha256', Str::random(64)),
            'token_expires_at' => now()->addHours(24),
            'token_revoked_at' => null,
            'attempt_count' => 0,
            'last_ip_address' => null,
            'last_user_agent' => null,
            'device_token_hash' => null,
            'started_at' => null,
            'last_seen_at' => null,
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn() => [
            'status' => 'completed',
            'started_at' => now()->subMinutes(10),
            'last_seen_at' => now(),
            'completed_at' => now(),
        ]);
    }
}
