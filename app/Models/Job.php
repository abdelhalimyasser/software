<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Enums\JobStatus; 

/**
 * Class Job
 *
 * The Job class represents a job opening in the application.
 * It provides functionality for managing job listings and their associated data.
 *
 * @version 1.0
 * @since 30-04-2026
 * @author Ali Samy
 */

class Job extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'department',
        'description',
        'status',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'status' => JobStatus::class, 
    ];

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class);
    }

    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => strtolower($value),
        );
    }
}