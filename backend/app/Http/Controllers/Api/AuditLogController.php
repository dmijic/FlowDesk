<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuditLogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        if (! $request->user()->hasPermission('view_reports') && ! $request->user()->hasPermission('manage_workflows')) {
            abort(403);
        }

        $query = AuditLog::query()->with('actor')->latest('id');

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->string('entity_type'));
        }

        if ($request->filled('entity_id')) {
            $query->where('entity_id', (int) $request->integer('entity_id'));
        }

        return AuditLogResource::collection($query->paginate(50));
    }
}
