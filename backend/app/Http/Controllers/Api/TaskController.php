<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskController extends Controller
{
    use AuthorizesRequests;

    private $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * List tasks for a project (Kanban)
     */
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $tasks = Task::where('project_id', $project->id)
            ->with(['assignee'])
            ->get();

        return TaskResource::collection($tasks);
    }

    /**
     * Store new task
     */
    public function store(StoreTaskRequest $request)
    {
        $this->authorize('create', [Task::class, $request->project_id]);

        $task = $this->taskService->create($request->validated());

        return new TaskResource($task);
    }

    /**
     * Update task details
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $task = $this->taskService->update($task, $request->validated());

        return new TaskResource($task);
    }

    /**
     * Update task status (Kanban drag)
     */
    public function updateStatus(UpdateTaskStatusRequest $request, Task $task)
    {
        $this->authorize('updateStatus', $task);

        $task = $this->taskService->updateStatus($task, $request->status);

        return new TaskResource($task);
    }

    /**
     * Delete task
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }
}
