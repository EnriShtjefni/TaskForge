<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Validation\ValidationException;

class TaskService
{
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task;
    }

    public function updateStatus(Task $task, string $newStatus): Task
    {
        $current = $task->status;
        $allowed = TaskStatus::transitions()[$current] ?? [];

        if (! in_array($newStatus, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => ['Invalid status transition.'],
            ]);
        }

        $task->update([
            'status' => $newStatus,
        ]);

        return $task;
    }
}
