<?php

use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RequestTypeController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::apiResource('users', UserController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('request-types', RequestTypeController::class)->parameters(['request-types' => 'requestType']);
    Route::apiResource('workflows', WorkflowController::class)->parameters(['workflows' => 'workflow']);

    Route::get('/requests', [ServiceRequestController::class, 'index']);
    Route::post('/requests', [ServiceRequestController::class, 'store']);
    Route::get('/requests/{requestModel}', [ServiceRequestController::class, 'show']);
    Route::post('/requests/{requestModel}/submit', [ServiceRequestController::class, 'submit']);

    Route::post('/requests/{requestModel}/attachments', [AttachmentController::class, 'store']);
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download']);

    Route::get('/approvals/inbox', [ApprovalController::class, 'inbox']);
    Route::post('/approvals/tasks/{task}/approve', [ApprovalController::class, 'approve']);
    Route::post('/approvals/tasks/{task}/reject', [ApprovalController::class, 'reject']);

    Route::get('/reports/summary', [ReportController::class, 'summary']);
    Route::get('/reports/requests.csv', [ReportController::class, 'csv']);

    Route::get('/audit-logs', [AuditLogController::class, 'index']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markRead']);
});
