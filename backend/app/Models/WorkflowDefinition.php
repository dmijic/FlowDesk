<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_type_id',
        'name',
        'version',
        'definition_json',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'definition_json' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function requestType(): BelongsTo
    {
        return $this->belongsTo(RequestType::class);
    }
}
