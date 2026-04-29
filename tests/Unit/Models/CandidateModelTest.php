<?php

namespace Tests\Unit\Models;

use App\Models\Candidate;
use App\Models\Enums\UserRole;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CandidateModelTest extends TestCase
{
    use DatabaseMigrations;

    public function test_candidate_fillable_includes_candidate_specific_fields(): void
    {
        $candidate = new Candidate();
        $fillable = $candidate->getFillable();

        $this->assertContains('resume_path', $fillable);
        $this->assertContains('docs_path', $fillable);
        $this->assertContains('skills', $fillable);
        $this->assertContains('experience_years', $fillable);
    }

    public function test_candidate_fillable_includes_parent_fields(): void
    {
        $candidate = new Candidate();
        $fillable = $candidate->getFillable();

        $this->assertContains('first_name', $fillable);
        $this->assertContains('last_name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
    }

    public function test_candidate_can_be_created_with_all_fields(): void
    {
        $candidate = Candidate::create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'phone_number' => '0100000000',
            'password' => 'password',
            'role' => UserRole::CANDIDATE->value,
            'resume_path' => 'resumes/jane.pdf',
            'docs_path' => 'documents/jane.zip',
            'skills' => ['PHP', 'Laravel'],
            'experience_years' => 3,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'role' => 'CANDIDATE',
            'experience_years' => 3,
        ]);
        $this->assertInstanceOf(Candidate::class, $candidate);
    }

    public function test_candidate_skills_stored_and_retrieved_as_array(): void
    {
        $candidate = Candidate::create([
            'first_name' => 'Skill',
            'last_name' => 'Test',
            'email' => 'skill@example.com',
            'phone_number' => '0100000001',
            'password' => 'password',
            'role' => UserRole::CANDIDATE->value,
            'skills' => ['Python', 'Django', 'FastAPI'],
        ]);

        $fresh = Candidate::find($candidate->id);
        $this->assertIsArray($fresh->skills);
        $this->assertEquals(['Python', 'Django', 'FastAPI'], $fresh->skills);
    }
}
