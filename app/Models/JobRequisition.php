<?php

namespace App\Models;

use App\Models\Enums\JobStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobRequisition extends Model
{
    protected $fillable = [
        'title',
        'description',
        'department',
        'created_by',
        'status',
        'status_updated_by'
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