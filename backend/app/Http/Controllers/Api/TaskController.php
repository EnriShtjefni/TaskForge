<?php

namespace App\Http\Controllers\Api;

use App\Events\TaskCreated;
use App\Events\TaskDeleted;
use App\Events\TaskUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use AuthorizesRequests;

    private $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * List tasks for a project (Kanban) with filtering and pagination.
     */
    public function index(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $query = Task::where('project_id', $project->id)
            ->with(['assignee', 'comments.user']);

        if ($assignedTo = $request->integer('assigned_to')) {
            $query->where('assigned_to', $assignedTo);
        }

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tasks = $query
            ->orderByDesc('created_at')
            ->paginate(20);

        return TaskResource::collection($tasks);
    }

    /**
     * Store new task
     */
    public function store(StoreTaskRequest $request)
    {
        $this->authorize('create', [Task::class, $request->project_id]);

        $task = $this->taskService->create($request->validated());
        $task->load(['assignee']);

        activity()
            ->causedBy($request->user())
            ->performedOn($task)
            ->withProperties(['name' => $task->name, 'project_id' => $task->project_id])
            ->log('task_created');

        TaskCreated::dispatch($task);

        return new TaskResource($task);
    }

    /**
     * Update task details
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $task = $this->taskService->update($task, $request->validated());
        $task->load(['assignee']);

        activity()
            ->causedBy($request->user())
            ->performedOn($task)
            ->withProperties(['name' => $task->name])
            ->log('task_updated');

        TaskUpdated::dispatch($task);

        return new TaskResource($task);
    }

    /**
     * Update task status (Kanban drag)
     */
    public function updateStatus(UpdateTaskStatusRequest $request, Task $task)
    {
        $this->authorize('updateStatus', $task);

        $task = $this->taskService->updateStatus($task, $request->status);
        $task->load(['assignee', 'comments.user']);

        activity()
            ->causedBy($request->user())
            ->performedOn($task)
            ->withProperties(['name' => $task->name, 'status' => $task->status])
            ->log('task_status_updated');

        TaskUpdated::dispatch($task);

        return new TaskResource($task);
    }

    /**
     * Delete task
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $taskId = $task->id;
        $projectId = $task->project_id;

        activity()
            ->causedBy(request()->user())
            ->performedOn($task)
            ->withProperties(['name' => $task->name, 'task_id' => $taskId])
            ->log('task_deleted');

        $task->delete();

        TaskDeleted::dispatch($taskId, $projectId);

        return response()->json(['message' => 'Task deleted']);
    }
}
