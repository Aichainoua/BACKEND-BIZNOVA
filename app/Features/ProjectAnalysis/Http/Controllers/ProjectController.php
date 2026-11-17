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
            $user->projects()
                ->latest('created_at')
                ->paginate(15)
        );
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        $project = $user->projects()->create([
            'sector_id' => $data['sector_id'],
            'name' => $data['name'],
            'launch_date' => $data['launch_date'],
            'horizon_months' => $data['horizon_months'] ?? 12,
            'country' => $data['country'] ?? null,
            'currency' => $data['currency'] ?? 'XAF',
            'activity_mode' => $data['activity_mode'],
        ]);

        return (new ProjectResource($project))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Project $project): ProjectResource
    {
        $project = Auth::user()
            ->projects()
            ->findOrFail($project->id);

        return new ProjectResource($project);
    }

    public function update(
        UpdateProjectRequest $request,
        Project $project
    ): ProjectResource {
        $project = Auth::user()
            ->projects()
            ->findOrFail($project->id);

        $data = $request->validated();

        $project->update($data);

        return new ProjectResource($project->refresh());
    }

    public function destroy(Project $project): Response
    {
        Auth::user()
            ->projects()
            ->findOrFail($project->id)
            ->delete();

        return response()->noContent();
    }
}
