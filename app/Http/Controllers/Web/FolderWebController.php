<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FolderWebController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('company.plan');
        $folders = Folder::withCount('documents')
            ->orderBy('name')
            ->get()
            ->toArray();

        return view('folders.index', [
            'folders' => $folders,
            'user'    => $user->toArray(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'color'       => 'nullable|string',
            'icon'        => 'nullable|string',
            'description' => 'nullable|string|max:500',
            'parent_id'   => 'nullable|integer|exists:folders,id',
        ]);

        $user = Auth::user();

        Folder::create([
            'company_id'  => $user->company_id,
            'user_id'     => $user->id,
            'name'        => $validated['name'],
            'color'       => $validated['color'] ?? '#6366f1',
            'icon'        => $validated['icon'] ?? 'folder',
            'description' => $validated['description'] ?? null,
            'parent_id'   => $validated['parent_id'] ?? null,
        ]);

        return back()->with('success', 'Folder "' . $validated['name'] . '" created successfully!');
    }

    public function destroy(int $id)
    {
        $folder = Folder::findOrFail($id);

        // Move documents to root
        $folder->documents()->update(['folder_id' => null]);
        $folder->children()->update(['parent_id' => null]);
        $folder->delete();

        return back()->with('success', 'Folder deleted. Documents moved to root.');
    }
}
