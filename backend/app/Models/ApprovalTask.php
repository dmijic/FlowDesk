<?php

namespace App\Models;

use App\Enums\ApprovalRule;
use App\Enums\ApprovalTaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'step_key',
        'step_name',
        'rule',
        'status',
        'assigned_to',
        'decided_by',
        'decided_at',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rule' => ApprovalRule::class,
            'status' => ApprovalTaskStatus::class,
            'decided_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
