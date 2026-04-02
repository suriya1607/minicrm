<?php

namespace App\Services;

use App\Models\Task;

class TaskService
{
    public function getTasks(array $filters, int $perPage)
    {
        $query = Task::query()->with(['assignedTo:id,name']);

        foreach ($filters as $field => $value) {
            if ($value === '' || $value === null) continue;
            match ($field) {
                'status'        => $query->where('status', $value),
                'priority'      => $query->where('priority', $value),
                'taskable_type' => $query->where('taskable_type', 'like', "%{$value}%"),
                'assigned_to'   => $query->where('assigned_to', $value),
                default         => $query->where($field, 'like', "%{$value}%"),
            };
        }

        return $query->orderBy('due_date')->paginate($perPage);
    }

    public function getTasksForSubject(string $type, int $id)
    {
        return Task::where('taskable_type', "App\\Models\\{$type}")
            ->where('taskable_id', $id)
            ->with('assignedTo:id,name')
            ->orderBy('due_date')
            ->get();
    }

    public function createTask(array $data): Task
    {
        $data['taskable_type'] = "App\\Models\\{$data['taskable_type']}";
        return Task::create($data);
    }

    public function updateTask(Task $task, array $data): Task
    {
        if (
            isset($data['status']) &&
            $data['status'] === 'completed' &&
            $task->status !== 'completed'
        ) {
            $data['completed_at'] = now();
        }

        $task->update($data);
        return $task;
    }

    public function deleteTask(Task $task): void
    {
        $task->delete();
    }
}