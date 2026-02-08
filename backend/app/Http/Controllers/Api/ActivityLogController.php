<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * List activity logs
     */
    public function index(Request $request)
    {
        $logs = Activity::query()
            ->with('causer')
            ->orderByDesc('created_at')
            ->paginate(10);

        return ActivityLogResource::collection($logs);
    }
}
