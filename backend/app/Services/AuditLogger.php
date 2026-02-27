<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     * @param  array<string, mixed>  $meta
     */
    public function log(?User $actor, string $action, Model|string $entity, ?array $before = null, ?array $after = null, array $meta = []): AuditLog
    {
        $entityType = $entity instanceof Model ? $entity::class : (string) $entity;
        $entityId = $entity instanceof Model ? (int) $entity->getKey() : 0;

        return AuditLog::create([
            'actor_id' => $actor?->id,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'before_json' => $before,
            'after_json' => $after,
            'meta_json' => $meta,
            'created_at' => now(),
        ]);
    }
}
