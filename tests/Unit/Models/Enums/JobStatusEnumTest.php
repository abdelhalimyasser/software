<?php

namespace Tests\Unit\Models\Enums;

use App\Models\Enums\JobStatus;
use PHPUnit\Framework\TestCase;

class JobStatusEnumTest extends TestCase
{
    public function test_all_statuses_have_correct_values(): void
    {
        $this->assertSame('PENDING', JobStatus::PENDING->value);
        $this->assertSame('APPROVED', JobStatus::APPROVED->value);
        $this->assertSame('REJECTED', JobStatus::REJECTED->value);
        $this->assertSame('CLOSED', JobStatus::CLOSED->value);
    }

    public function test_status_count_is_four(): void
    {
        $this->assertCount(4, JobStatus::cases());
    }

    public function test_status_can_be_created_from_value(): void
    {
        $this->assertSame(JobStatus::PENDING, JobStatus::from('PENDING'));
        $this->assertSame(JobStatus::APPROVED, JobStatus::from('APPROVED'));
    }

    public function test_invalid_status_throws_value_error(): void
    {
        $this->expectException(\ValueError::class);
        JobStatus::from('INVALID');
    }
}
