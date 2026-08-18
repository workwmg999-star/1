<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $docs = $request->user()->documents()->latest()->get();
        return response()->json($docs);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'pages' => 'required|array|min:1',
            'pages.*' => 'string',
        ]);

        $doc = $request->user()->documents()->create([
            'title' => $validated['title'] ?? 'Untitled',
            'pages' => $validated['pages'],
        ]);

        return response()->json($doc, 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $doc = $request->user()->documents()->findOrFail($id);
        return response()->json($doc);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $doc = $request->user()->documents()->findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'pages' => 'nullable|array|min:1',
            'pages.*' => 'string',
        ]);

        $doc->update($validated);
        return response()->json($doc);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $request->user()->documents()->findOrFail($id)->delete();
        return response()->json(['message' => 'deleted']);
    }
}
