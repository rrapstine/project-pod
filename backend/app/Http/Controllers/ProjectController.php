<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Workspace;
use App\Traits\ApiResponses;

class ProjectController extends Controller
{
    use ApiResponses;

    /**
     * Display a listing of the resource.
     */
    public function index(?Workspace $workspace = null)
    {
        if ($workspace) {
            $this->authorize('view', $workspace);
            $projects = $workspace->projects()->with('workspace')->get();

            return ProjectResource::collection($projects);
        }

        $projects = $this->currentUser()->projects()->with('workspace')->get();

        return ProjectResource::collection($projects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request, Workspace $workspace)
    {
        $this->authorize('view', $workspace);

        $project = $workspace->projects()->create([
            ...$request->validated(),
            'user_id' => $this->currentUser()->id,
        ]);
        $project->load('workspace');

        return $this->success($project->name.' project created successfully', ProjectResource::make($project), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);
        $project->load('workspace');

        return ProjectResource::make($project);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);
        $project->update($request->validated());
        $project->load('workspace');

        return $this->success($project->name.' project updated successfully', ProjectResource::make($project));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $name = $project->name;
        $project->delete();

        return $this->success($name.' project deleted successfully', [], 204);
    }
}
