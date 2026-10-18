<?php

namespace App\Console\Commands;

use App\Domains\Assignments\Services\AssignmentVerificationService;
use Illuminate\Console\Command;

class ExpireAssignmentQuizzes extends Command
{
    protected $signature = 'assignments:expire-quizzes';

    protected $description = 'Expire assignment quizzes whose time limit has elapsed';

    public function handle(AssignmentVerificationService $verificationService): int
    {
        $expiredCount = $verificationService->expireDueQuizzes();

        $this->info(
            "Assignment quiz expiration completed. Expired: {$expiredCount}"
        );

        return self::SUCCESS;
    }
}
