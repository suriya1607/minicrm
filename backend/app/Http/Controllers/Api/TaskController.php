<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(protected TaskService $taskService) {}

    public function index(Request $request)
    {
        // ?for_type=Contact&for_id=5 → tasks for a specific record
        if ($request->filled('for_type') && $request->filled('for_id')) {
            $tasks = $this->taskService->getTasksForSubject(
                $request->for_type,
                $request->for_id
            );
            return response()->json([
                'status' => true,
                'data'   => TaskResource::collection($tasks),
            ]);
        }

        $tasks = $this->taskService->getTasks(
            $request->input('filter', []),
            $request->get('per_page', 15)
        );

        return response()->json([
            'status'  => true,
            'message' => 'Tasks fetched successfully',
            'data'    => TaskResource::collection($tasks),
            'meta'    => [
                'current_page' => $tasks->currentPage(),
                'last_page'    => $tasks->lastPage(),
                'total'        => $tasks->total(),
                'per_page'     => $tasks->perPage(),
            ],
        ]);
    }

    public function store(StoreTaskRequest $request)
    {
        $task = $this->taskService->createTask($request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Task created successfully',
            'data'    => new TaskResource($task->load('assignedTo')),
        ], 201);
    }

    public function show(Task $task)
    {
        return response()->json([
            'status' => true,
            'data'   => new TaskResource($task->load('assignedTo')),
        ]);
    }

    public function update(StoreTaskRequest $request, Task $task)
    {
        $updated = $this->taskService->updateTask($task, $request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Task updated successfully',
            'data'    => new TaskResource($updated->load('assignedTo')),
        ]);
    }

    public function destroy(Task $task)
    {
        $this->taskService->deleteTask($task);
        return response()->json(['status' => true, 'message' => 'Task deleted']);
    }
}