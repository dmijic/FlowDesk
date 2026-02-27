<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttachmentRequest;
use App\Models\RequestAttachment;
use App\Models\ServiceRequest;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function store(StoreAttachmentRequest $request, ServiceRequest $requestModel): JsonResponse
    {
        $this->authorize('view', $requestModel);

        $file = $request->file('file');
        $path = $file->store("requests/{$requestModel->id}");

        $attachment = RequestAttachment::create([
            'request_id' => $requestModel->id,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $file->getSize(),
            'path' => $path,
            'uploaded_by' => $request->user()->id,
            'created_at' => now(),
        ]);

        $this->auditLogger->log(
            $request->user(),
            'request.attachment_added',
            $attachment,
            null,
            $attachment->toArray(),
            ['request_id' => $requestModel->id]
        );

        return response()->json([
            'id' => $attachment->id,
            'original_name' => $attachment->original_name,
            'mime' => $attachment->mime,
            'size' => $attachment->size,
        ], 201);
    }

    public function download(RequestAttachment $attachment)
    {
        $this->authorize('view', $attachment->request);

        if (! Storage::exists($attachment->path)) {
            abort(404, 'Attachment file is missing.');
        }

        return Storage::download($attachment->path, $attachment->original_name);
    }
}
