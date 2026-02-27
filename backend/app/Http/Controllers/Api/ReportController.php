<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function summary(): JsonResponse
    {
        Gate::authorize('view_reports');

        $counts = ServiceRequest::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $avgHours = (float) ServiceRequest::query()
            ->whereNotNull('submitted_at')
            ->whereNotNull('decided_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, submitted_at, decided_at)) as avg_hours')
            ->value('avg_hours');

        $topTypes = DB::table('requests')
            ->join('request_types', 'request_types.id', '=', 'requests.type_id')
            ->select('request_types.name', DB::raw('COUNT(*) as total'))
            ->groupBy('request_types.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return response()->json([
            'counts_by_status' => $counts,
            'average_decision_time_hours' => round($avgHours, 2),
            'top_request_types' => $topTypes,
        ]);
    }

    public function csv()
    {
        Gate::authorize('view_reports');

        $rows = ServiceRequest::query()
            ->with(['type', 'department', 'creator'])
            ->latest('id')
            ->limit(1000)
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="requests.csv"',
        ];

        return response()->stream(function () use ($rows): void {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['id', 'title', 'type', 'department', 'priority', 'status', 'requester', 'submitted_at', 'decided_at']);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->id,
                    $row->title,
                    $row->type?->name,
                    $row->department?->name,
                    $row->priority->value,
                    $row->status->value,
                    $row->creator?->email,
                    $row->submitted_at?->toDateTimeString(),
                    $row->decided_at?->toDateTimeString(),
                ]);
            }

            fclose($out);
        }, 200, $headers);
    }
}
