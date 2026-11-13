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
                ->latest('date_creation')
                ->paginate(15)
        );
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        $project = $user->projects()->create([
            'id_secteur' => $data['sector_id'],
            'nom' => $data['name'],
            'date_lancement' => $data['launch_date'],
            'horizon_mois' => $data['horizon_months'] ?? 12,
            'pays' => $data['country'] ?? null,
            'devise' => $data['currency'] ?? 'XAF',
            'mode_activite' => $data['activity_mode'],
        ]);

        return (new ProjectResource($project))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Project $project): ProjectResource
    {
        $project = Auth::user()
            ->projects()
            ->findOrFail($project->id_projet);

        return new ProjectResource($project);
    }

    public function update(
        UpdateProjectRequest $request,
        Project $project
    ): ProjectResource {
        $project = Auth::user()
            ->projects()
            ->findOrFail($project->id_projet);

        $data = $request->validated();

        $mapping = [
            'sector_id' => 'id_secteur',
            'name' => 'nom',
            'launch_date' => 'date_lancement',
            'horizon_months' => 'horizon_mois',
            'country' => 'pays',
            'currency' => 'devise',
            'activity_mode' => 'mode_activite',
        ];

        $updates = [];

        foreach ($mapping as $apiField => $databaseField) {
            if (array_key_exists($apiField, $data)) {
                $updates[$databaseField] = $data[$apiField];
            }
        }

        $project->update($updates);

        return new ProjectResource($project->refresh());
    }

    public function destroy(Project $project): Response
    {
        Auth::user()
            ->projects()
            ->findOrFail($project->id_projet)
            ->delete();

        return response()->noContent();
    }
}
