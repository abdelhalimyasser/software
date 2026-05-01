<?php

namespace App\Models;

use App\Models\Enums\JobStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class JobPost
 *
 * The JobPost class represents a job opening in the application.
 * It provides functionality for managing job listings and their associated data.
 *
 * @version 1.0
 * @since 01-05-2026
 * @author Ali Samy
 */
class JobPost extends Model
{
    protected $fillable = [
        'title',
        'description',
        'department',
        'location',
        'skills',
        'experience_level',
        'created_by',
        'status',
        'status_updated_by',
        'status_reason'
    ];

    protected function casts(): array
    {
        return [
            'status' => JobStatus::class,
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusUpdater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_updated_by');
    }
}
