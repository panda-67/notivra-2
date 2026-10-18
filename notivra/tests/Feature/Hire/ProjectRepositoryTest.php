<?php

namespace Tests\Feature\Hire;

use App\Domains\Hire\Repositories\ProjectRepository;
use App\Enums\UserRole;
use App\Models\HireDraft;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class ProjectRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private function createDraft(
        array $data = [],
        int $step = 3,
        string $status = 'draft',
    ): HireDraft {
        return HireDraft::create([
            'data' => $data,
            'step' => $step,
            'status' => $status,
        ]);
    }

    private function validProjectData(): array
    {
        $service = Service::factory()->create();

        return [
            'service_id' => $service->id,
            'phone' => '08123456789',
            'institution' => 'Universitas Pengujian',
            'level' => 'undergraduate',
            'field' => 'Biology',
            'topic' => 'Research topic',
            'description' => 'Project description',
            'output' => 'Research report',
            'deadline' => now()->addDays(30)->toDateString(),
            'address' => 'Test address',
            'city' => 'Banda Aceh',
            'postal_code' => '23111',
            'willing_dp' => false,
            'budget' => 1000000,
        ];
    }

    private function authenticateUser(): User
    {
        $user = User::factory()->create([
            'role' => UserRole::RESEARCHER,
        ]);

        Auth::login($user);

        return $user;
    }

    public function test_it_submits_a_draft_and_creates_a_project(): void
    {
        $user = $this->authenticateUser();
        $data = $this->validProjectData();
        $draft = $this->createDraft($data);

        $project = app(ProjectRepository::class)
            ->submitProject($draft, []);

        $this->assertInstanceOf(Project::class, $project);
        $this->assertSame($user->id, $project->user_id);
        $this->assertSame($user->name, $project->name);
        $this->assertSame($user->email, $project->email);
        $this->assertSame('pending', $project->status);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('hire_drafts', [
            'id' => $draft->id,
            'status' => 'submitted',
        ]);
    }

    public function test_it_does_not_submit_a_draft_twice(): void
    {
        $this->authenticateUser();

        $draft = $this->createDraft(
            $this->validProjectData(),
            status: 'submitted',
        );

        $result = app(ProjectRepository::class)
            ->submitProject($draft, []);

        $this->assertNull($result);
        $this->assertDatabaseCount('projects', 0);
    }

    public function test_it_rejects_a_draft_with_an_invalid_status(): void
    {
        $this->authenticateUser();

        $draft = $this->createDraft(
            $this->validProjectData(),
            status: 'cancelled',
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(
            'Status draft tidak memungkinkan submission.',
        );

        app(ProjectRepository::class)->submitProject($draft, []);
    }

    public function test_it_rejects_a_draft_before_step_three(): void
    {
        $this->authenticateUser();

        $draft = $this->createDraft(
            $this->validProjectData(),
            step: 2,
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(
            'Lengkapi semua step sebelum mengirim.',
        );

        app(ProjectRepository::class)->submitProject($draft, []);
    }

    public function test_it_rejects_submission_without_an_authenticated_user(): void
    {
        Auth::logout();

        $draft = $this->createDraft($this->validProjectData());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Pengguna tidak terautentikasi.',
        );

        app(ProjectRepository::class)->submitProject($draft, []);
    }

    public function test_it_rolls_back_when_a_draft_attachment_is_missing(): void
    {
        Storage::fake('local');

        $this->authenticateUser();

        $data = $this->validProjectData();
        $data['files'] = 'temporary/missing-document.pdf';

        $draft = $this->createDraft($data);

        try {
            app(ProjectRepository::class)->submitProject($draft, []);
            $this->fail('Expected a missing attachment exception.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Lampiran draft tidak ditemukan.',
                $exception->getMessage(),
            );
        }

        $this->assertDatabaseCount('projects', 0);

        $this->assertDatabaseHas('hire_drafts', [
            'id' => $draft->id,
            'status' => 'draft',
        ]);
    }
}
