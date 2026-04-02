<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Services\ActivityService;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function __construct(protected ActivityService $activityService) {}

    // GET /activities?for_type=Contact&for_id=5
    public function index(Request $request)
    {
        $query = Activity::with('user:id,name');

        if ($request->filled('for_type') && $request->filled('for_id')) {
            $query->where('subject_type', "App\\Models\\{$request->for_type}")
                  ->where('subject_id', $request->for_id);
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'status' => true,
            'data'   => ActivityResource::collection($activities),
            'meta'   => [
                'current_page' => $activities->currentPage(),
                'last_page'    => $activities->lastPage(),
                'total'        => $activities->total(),
            ],
        ]);
    }

    // POST /activities  — manual note / call log
    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_type' => 'required|in:Contact,Lead,Deal',
            'subject_id'   => 'required|integer',
            'type'         => 'required|in:note,call,email,meeting',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
        ]);

        $data['subject_type'] = "App\\Models\\{$data['subject_type']}";

        $activity = $this->activityService->log(
            (object)['id' => $data['subject_id']],
            $data['type'],
            $data['title'],
            $data['description'] ?? ''
        );

        // re-fetch with correct subject_type
        $activity->subject_type = $data['subject_type'];
        $activity->subject_id   = $data['subject_id'];
        $activity->save();

        return response()->json([
            'status'  => true,
            'message' => 'Activity logged',
            'data'    => new ActivityResource($activity),
        ], 201);
    }
}