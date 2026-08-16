<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Resources\FolderResource;
use App\Models\Folder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    /**
     * GET /api/v1/folders
     * List all top-level folders (or by parent_id).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Folder::withCount('documents');

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        } else {
            $query->whereNull('parent_id');
        }

        $folders = $query->with('children')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => FolderResource::collection($folders),
        ]);
    }

    /**
     * POST /api/v1/folders
     */
    public function store(StoreFolderRequest $request): JsonResponse
    {
        $this->authorize('create', Folder::class);

        $user = $request->user();

        // If parent_id is given, ensure it belongs to this company
        if ($request->filled('parent_id')) {
            $parent = Folder::findOrFail($request->parent_id);
            $this->authorize('view', $parent);
        }

        $folder = Folder::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'color' => $request->color ?? '#6366f1',
            'icon' => $request->icon ?? 'folder',
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Folder created successfully.',
            'data' => new FolderResource($folder),
        ], 201);
    }

    /**
     * GET /api/v1/folders/{folder}
     */
    public function show(Folder $folder): JsonResponse
    {
        $this->authorize('view', $folder);

        $folder->load('children', 'documents')->loadCount('documents');

        return response()->json([
            'success' => true,
            'data' => new FolderResource($folder),
        ]);
    }

    /**
     * PUT /api/v1/folders/{folder}
     */
    public function update(Request $request, Folder $folder): JsonResponse
    {
        $this->authorize('update', $folder);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'parent_id' => ['nullable', 'integer', 'exists:folders,id'],
        ]);

        // Prevent moving folder into itself or its own children
        if (isset($validated['parent_id']) && $validated['parent_id'] == $folder->id) {
            return response()->json([
                'success' => false,
                'message' => 'A folder cannot be its own parent.',
            ], 422);
        }

        $folder->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Folder updated.',
            'data' => new FolderResource($folder->fresh()),
        ]);
    }

    /**
     * DELETE /api/v1/folders/{folder}
     */
    public function destroy(Folder $folder): JsonResponse
    {
        $this->authorize('delete', $folder);

        // Move documents in this folder to root (unfiled)
        $folder->documents()->update(['folder_id' => null]);

        // Move sub-folders to root
        $folder->children()->update(['parent_id' => null]);

        $folder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Folder deleted. Documents moved to root.',
        ]);
    }
}
