<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // Create task
    public function store(StoreTaskRequest $request)
    {
        $data = $request->validated();

        $assignee = User::where('email', $data['assignee_email'])->first();
        // exists rule already ensures it's found but extra safety
        if (! $assignee) {
            return response()->json(['message' => 'Assignee email not found'], 404);
        }

        $task = Task::create([
            'creator_id' => $request->user()->id,
            'assignee_id' => $assignee->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'],
            'priority' => $data['priority'] ?? 'medium',
            'is_completed' => false,
        ]);

        return (new TaskResource($task->load(['creator', 'assignee'])))->response()->setStatusCode(201);
    }

    // List tasks assigned to current user, with optional filters
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $query = Task::with(['creator', 'assignee'])
            ->where('assignee_id', $userId)
            ->orderBy('due_date', 'asc');

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->query('priority'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->query('status');
            $today = Carbon::today()->toDateString();

            if ($status === 'done') {
                $query->where('is_completed', true);
            } elseif ($status === 'missed') {
                $query->where('is_completed', false)
                    ->whereDate('due_date', '<', $today);
            } elseif ($status === 'due_today') {
                $query->where('is_completed', false)
                    ->whereDate('due_date', $today);
            } elseif ($status === 'upcoming') {
                $query->where('is_completed', false)
                    ->whereDate('due_date', '>', $today);
            }
        }

        // Pagination optional: ?per_page=10
        $perPage = (int) $request->query('per_page', 0);
        if ($perPage > 0) {
            $paginated = $query->paginate($perPage);
            return TaskResource::collection($paginated)->response();
        }

        $tasks = $query->get();
        return TaskResource::collection($tasks);
    }

    // Show single task (allowed if assignee OR creator)
    public function show(Request $request, Task $task)
    {
        $userId = $request->user()->id;
        if ($task->assignee_id !== $userId && $task->creator_id !== $userId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return new TaskResource($task->load(['creator', 'assignee']));
    }

    // Update details (only assignee can edit details)
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $userId = $request->user()->id;

        if ($task->assignee_id !== $userId) {
            return response()->json(['message' => 'Forbidden: only assignee can edit task details'], 403);
        }

        $task->fill($request->validated());
        $task->save();

        return new TaskResource($task->fresh(['creator', 'assignee']));
    }

    // Delete (creator OR assignee)
    public function destroy(Request $request, Task $task)
    {
        $userId = $request->user()->id;

        if ($task->assignee_id !== $userId && $task->creator_id !== $userId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $task->delete();
        return response()->json(['message' => 'Task deleted'], 200);
    }

    // Toggle completion (only assignee)
    public function toggle(Request $request, Task $task)
    {
        $userId = $request->user()->id;

        if ($task->assignee_id !== $userId) {
            return response()->json(['message' => 'Forbidden: only assignee can toggle completion'], 403);
        }

        $task->is_completed = ! $task->is_completed;
        $task->save();

        return new TaskResource($task->fresh(['creator', 'assignee']));
    }

    // Assign / Reassign (only creator can assign/reassign)
    public function assign(Request $request, Task $task)
    {
        $userId = $request->user()->id;

        if ($task->creator_id !== $userId) {
            return response()->json(['message' => 'Forbidden: only creator can assign/reassign'], 403);
        }

        $request->validate([
            'assignee_email' => 'required|email|exists:users,email',
        ]);

        $assignee = User::where('email', $request->input('assignee_email'))->first();
        if (! $assignee) {
            return response()->json(['message' => 'Assignee email not found'], 404);
        }

        $task->assignee_id = $assignee->id;
        $task->save();

        return new TaskResource($task->fresh(['creator', 'assignee']));
    }
}
