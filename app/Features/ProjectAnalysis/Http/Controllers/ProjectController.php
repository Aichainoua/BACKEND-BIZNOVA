<?php

namespace App\Features\ProjectAnalysis\Http\Controllers;

use App\Features\ProjectAnalysis\Http\Requests\StoreProjectRequest;
use App\Features\ProjectAnalysis\Http\Requests\UpdateProjectRequest;
use App\Features\ProjectAnalysis\Http\Resources\ProjectResource;
use App\Features\ProjectAnalysis\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $user = Auth::user();

        return ProjectResource::collection(
            $user->projects()->latest('id')->paginate(15),
        );
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $user = Auth::user();
        $project = $user->projects()->create($request->validated());

        return (new ProjectResource($project))->response()->setStatusCode(201);
    }

    public function show(Project $project): ProjectResource
    {
        $user = Auth::user();
        $project = $user->projects()->findOrFail($project->id);

        return new ProjectResource($project);
    }

    public function update(UpdateProjectRequest $request, Project $project): ProjectResource
    {
        $user = Auth::user();
        $project = $user->projects()->findOrFail($project->id);
        $project->update($request->validated());

        return new ProjectResource($project->refresh());
    }

    public function destroy(Project $project): Response
    {
        $user = Auth::user();
        $user->projects()->findOrFail($project->id)->delete();

        return response()->noContent();
    }
}
