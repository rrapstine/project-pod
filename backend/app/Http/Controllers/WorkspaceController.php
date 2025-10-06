<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkspaceRequest;
use App\Http\Requests\UpdateWorkspaceRequest;
use App\Http\Resources\WorkspaceResource;
use App\Models\Workspace;
use App\Traits\ApiResponses;

class WorkspaceController extends Controller
{
    use ApiResponses;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $workspaces = $this->currentUser()->workspaces;

        return WorkspaceResource::collection($workspaces);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkspaceRequest $request)
    {
        $workspace = $this->currentUser()->workspaces()->create($request->validated());

        return $this->success($workspace->name.' workspace created successfully', WorkspaceResource::make($workspace), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Workspace $workspace)
    {
        $this->authorize('view', $workspace);

        return WorkspaceResource::make($workspace);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWorkspaceRequest $request, Workspace $workspace)
    {
        $this->authorize('update', $workspace);
        $workspace->update($request->validated());

        return $this->success($workspace->name.' workspace updated successfully', WorkspaceResource::make($workspace));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workspace $workspace)
    {
        $this->authorize('delete', $workspace);

        $name = $workspace->name;
        $workspace->delete();

        return $this->success($name.' workspace deleted successfully', [], 204);
    }
}
