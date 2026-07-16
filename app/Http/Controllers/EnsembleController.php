<?php

namespace App\Http\Controllers;

use App\Models\Ensemble;
use App\Models\EnsembleFolder;
use App\Models\MusicScore;
use App\Models\Rehearsal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EnsembleController extends Controller
{
    public function index()
    {
        $ensembles = Ensemble::withCount('members')->get();
        return response()->json(['status' => true, 'data' => $ensembles]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:ensembles,name',
            'cif' => 'required|string|max:20|unique:ensembles,cif',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $ensemble = Ensemble::create([
            'name' => $request->name,
            'cif' => $request->cif,
            'description' => $request->description,
            'owner_id' => $request->user()->id,
        ]);

        // Add creator as admin
        $ensemble->members()->attach($request->user()->id, ['role' => 'administrador']);

        return response()->json(['status' => true, 'data' => $ensemble], 201);
    }

    public function show(Ensemble $ensemble)
    {
        $ensemble->load('members', 'folders', 'rehearsals');
        return response()->json(['status' => true, 'data' => $ensemble]);
    }

    public function update(Request $request, Ensemble $ensemble)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:ensembles,name,' . $ensemble->id,
            'cif' => 'sometimes|string|max:20|unique:ensembles,cif,' . $ensemble->id,
            'description' => 'nullable|string',
            'status' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $ensemble->update($request->only(['name', 'cif', 'description', 'status']));

        return response()->json(['status' => true, 'data' => $ensemble]);
    }

    public function destroy(Ensemble $ensemble)
    {
        $ensemble->delete();
        return response()->json(['status' => true, 'message' => 'Ensemble deleted']);
    }

    // Members
    public function members(Ensemble $ensemble)
    {
        $members = $ensemble->members()->withPivot('role', 'status')->get();
        return response()->json(['status' => true, 'data' => $members]);
    }

    public function addMember(Request $request, Ensemble $ensemble)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:archivero,administrador,maestro,usuario',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        if ($ensemble->members()->where('user_id', $request->user_id)->exists()) {
            return response()->json(['status' => false, 'message' => 'User is already a member'], 409);
        }

        $ensemble->members()->attach($request->user_id, [
            'role' => $request->role,
        ]);

        return response()->json(['status' => true, 'message' => 'Member added'], 201);
    }

    public function updateMember(Request $request, Ensemble $ensemble, User $user)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:archivero,administrador,maestro,usuario',
            'status' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $ensemble->members()->updateExistingPivot($user->id, $request->only(['role', 'status']));

        return response()->json(['status' => true, 'message' => 'Member updated']);
    }

    public function removeMember(Ensemble $ensemble, User $user)
    {
        $ensemble->members()->detach($user->id);
        return response()->json(['status' => true, 'message' => 'Member removed']);
    }

    // Folders
    public function folders(Ensemble $ensemble)
    {
        $folders = $ensemble->folders()->with('children')->whereNull('parent_id')->get();
        return response()->json(['status' => true, 'data' => $folders]);
    }

    private function buildFolderPath(string $name, ?int $parentId): string
    {
        $path = $name;
        while ($parentId) {
            $parent = EnsembleFolder::findOrFail($parentId);
            $path = $parent->name . '/' . $path;
            $parentId = $parent->parent_id;
        }
        return $path;
    }

    private function validatePathLength(string $name, ?int $parentId, int $ensembleId): void
    {
        $basePath = storage_path("app/music_scores/ensembles/{$ensembleId}/");
        $fullPath = $basePath . $this->buildFolderPath($name, $parentId);

        if (strlen($fullPath) > 4096) {
            throw ValidationException::withMessages([
                'name' => 'La ruta completa excede el límite de 4096 caracteres',
            ]);
        }
    }

    public function storeFolder(Request $request, Ensemble $ensemble)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:ensemble_folders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $this->validatePathLength($request->name, $request->parent_id, $ensemble->id);

        $folder = EnsembleFolder::create([
            'ensemble_id' => $ensemble->id,
            'name' => $request->name,
            'parent_id' => $request->parent_id,
        ]);

        return response()->json(['status' => true, 'data' => $folder], 201);
    }

    public function updateFolder(Request $request, EnsembleFolder $folder)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $this->validatePathLength($request->name, $folder->parent_id, $folder->ensemble_id);

        $folder->update($request->only(['name']));
        return response()->json(['status' => true, 'data' => $folder]);
    }

    public function destroyFolder(EnsembleFolder $folder)
    {
        $folder->delete();
        return response()->json(['status' => true, 'message' => 'Folder deleted']);
    }

    // Scores
    public function scores(Ensemble $ensemble)
    {
        $scores = $ensemble->scores()->with('files', 'composers')->get();
        return response()->json(['status' => true, 'data' => $scores]);
    }

    public function storeScore(Request $request, Ensemble $ensemble)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'ensemble_folder_id' => 'nullable|exists:ensemble_folders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $score = MusicScore::create([
            'name' => $request->name,
            'ensemble_id' => $ensemble->id,
            'uploaded_by' => $request->user()->id,
            'ensemble_folder_id' => $request->ensemble_folder_id,
        ]);

        return response()->json(['status' => true, 'data' => $score], 201);
    }

    public function bulkUploadScores(Request $request, Ensemble $ensemble)
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array',
            'files.*' => 'required|file|mimes:pdf|max:51200',
            'ensemble_folder_id' => 'nullable|exists:ensemble_folders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $uploaded = [];
        $errors = [];

        foreach ($request->file('files') as $file) {
            try {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                $score = MusicScore::create([
                    'name' => $originalName,
                    'ensemble_id' => $ensemble->id,
                    'uploaded_by' => $request->user()->id,
                    'ensemble_folder_id' => $request->ensemble_folder_id,
                ]);

                $realName = date('U') . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $tempPath = $file->storeAs('temp', $realName, 'local');
                $fileContent = Storage::disk('local')->get($tempPath);

                Storage::disk('Wasabi')->put('music_score/' . $realName, $fileContent);
                Storage::disk('local')->delete($tempPath);

                $score->files()->create([
                    'path' => 'music_score/' . $realName,
                    'storagePlace' => 'Wasabisys',
                    'extension' => $file->getClientOriginalExtension(),
                ]);

                $uploaded[] = ['id' => $score->id, 'name' => $originalName];
            } catch (\Exception $e) {
                $errors[] = ['file' => $file->getClientOriginalName(), 'error' => $e->getMessage()];
            }
        }

        return response()->json([
            'status' => true,
            'data' => [
                'uploaded' => $uploaded,
                'errors' => $errors,
            ],
        ]);
    }

    // Rehearsals
    public function rehearsals(Ensemble $ensemble)
    {
        $rehearsals = $ensemble->rehearsals()->with('instructor')->orderBy('date', 'desc')->get();
        return response()->json(['status' => true, 'data' => $rehearsals]);
    }

    public function storeRehearsal(Request $request, Ensemble $ensemble)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'instructor_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $rehearsal = Rehearsal::create([
            'ensemble_id' => $ensemble->id,
            'title' => $request->title,
            'date' => $request->date,
            'time' => $request->time,
            'location' => $request->location,
            'instructor_id' => $request->instructor_id,
            'notes' => $request->notes,
        ]);

        return response()->json(['status' => true, 'data' => $rehearsal], 201);
    }

    public function updateRehearsal(Request $request, Rehearsal $rehearsal)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'date' => 'sometimes|date',
            'time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'instructor_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'status' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $rehearsal->update($request->only(['title', 'date', 'time', 'location', 'instructor_id', 'notes', 'status']));
        return response()->json(['status' => true, 'data' => $rehearsal]);
    }

    public function destroyRehearsal(Rehearsal $rehearsal)
    {
        $rehearsal->delete();
        return response()->json(['status' => true, 'message' => 'Rehearsal deleted']);
    }

    // User's ensembles
    public function myEnsembles(Request $request)
    {
        $ensembles = $request->user()->ensembles()->withPivot('role')->get();
        return response()->json(['status' => true, 'data' => $ensembles]);
    }

    // Find user by email for member invitation
    public function lookupUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first(['id', 'name', 'email']);

        return response()->json(['status' => true, 'data' => $user]);
    }

    // User's ensemble status (for premium logic)
    public function ensembleStatus(Request $request)
    {
        $user = $request->user();
        $isMember = $user->ensembles()->where('ensemble_user.status', true)->exists();

        return response()->json([
            'status' => true,
            'data' => [
                'is_ensemble_member' => $isMember,
            ],
        ]);
    }
}
