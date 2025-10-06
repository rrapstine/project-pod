<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Traits\ApiResponses;

class TaskController extends Controller
{
    use ApiResponses;

    /**
     * Display a listing of the resource.
     */
    public function index(?Project $project = null)
    {
        if ($project) {
            $this->authorize('view', $project);
            $tasks = $project->tasks()->with('project')->get();

            return TaskResource::collection($tasks);
        }

        $tasks = $this->currentUser()->tasks()->with('project')->get();

        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request, Project $project)
    {
        $this->authorize('view', $project);

        $task = $project->tasks()->create([
            ...$request->validated(),
            'user_id' => $this->currentUser()->id,
        ]);
        $task->load('project');

        return $this->success($task->title.' task created successfully', TaskResource::make($task), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);
        $task->load('project');

        return TaskResource::make($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);
        $task->update($request->validated());
        $task->load('project');

        return $this->success($task->title.' task updated successfully', TaskResource::make($task));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $title = $task->title;
        $task->delete();

        return $this->success($title.' task deleted successfully', [], 204);
    }
}
