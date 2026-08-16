<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\DocumentFile;
use App\Services\DocumentStorageService;
use App\Services\SubscriptionLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function __construct(
        private DocumentStorageService $storageService,
        private SubscriptionLimitService $limitService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Document::with(['user', 'folder', 'files'])
            ->latest();

        if ($request->has('folder_id')) {
            if ($request->folder_id === 'root' || $request->folder_id === null) {
                $query->whereNull('folder_id');
            } else {
                $query->where('folder_id', $request->folder_id);
            }
        }

        if ($request->filled('file_type')) {
            $query->where('file_type', $request->file_type);
        }

        if ($request->has('is_archived')) {
            $query->where('is_archived', filter_var($request->is_archived, FILTER_VALIDATE_BOOLEAN));
        } else {
            $query->where('is_archived', false);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $documents = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => DocumentResource::collection($documents),
            'meta'    => [
                'current_page' => $documents->currentPage(),
                'last_page'    => $documents->lastPage(),
                'per_page'     => $documents->perPage(),
                'total'        => $documents->total(),
            ],
        ]);
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $this->authorize('create', Document::class);

        $user    = $request->user();
        $company = $user->company;

        $hasSingleFile = $request->hasFile('file');
        $hasMultiFiles = $request->hasFile('files');

        // Calculate total upload size
        $totalBytes = 0;
        if ($hasSingleFile) {
            $totalBytes += $request->file('file')->getSize();
        }
        if ($hasMultiFiles) {
            foreach ($request->file('files') as $f) {
                $totalBytes += $f->getSize();
            }
        }

        // Check subscription storage limit
        if (!$this->limitService->canUploadFile($company, $totalBytes)) {
            return response()->json([
                'success' => false,
                'message' => 'Storage limit exceeded. Please upgrade your subscription plan.',
            ], 402);
        }

        // Check document count limit
        if (!$this->limitService->canAddDocument($company)) {
            return response()->json([
                'success' => false,
                'message' => 'Document count limit reached. Please upgrade your subscription plan.',
            ], 402);
        }

        return DB::transaction(function () use ($request, $user, $company, $hasSingleFile, $hasMultiFiles, $totalBytes) {
            $primaryFile = $hasSingleFile ? $request->file('file') : $request->file('files')[0];
            $fileSize = $primaryFile->getSize();
            $mimeType = $primaryFile->getMimeType();
            $fileType = str_contains($mimeType, 'pdf') ? 'pdf' : 'image';

            $filePath = $this->storageService->store($primaryFile, $company, 'documents');

            $pagesCount = 1;
            if ($hasMultiFiles) {
                $pagesCount = count($request->file('files'));
            } elseif ($request->hasFile('pages')) {
                $pagesCount = 1 + count($request->file('pages'));
            }

            $document = Document::create([
                'company_id'  => $company->id,
                'user_id'     => $user->id,
                'folder_id'   => $request->folder_id,
                'title'       => $request->title,
                'description' => $request->description,
                'file_path'   => $filePath,
                'file_type'   => $fileType,
                'mime_type'   => $mimeType,
                'size_bytes'  => $totalBytes,
                'pages_count' => $pagesCount,
                'metadata'    => $request->metadata,
            ]);

            // Always create page 1 DocumentFile for the primary file
            if ($hasSingleFile && !$request->hasFile('pages')) {
                DocumentFile::create([
                    'document_id' => $document->id,
                    'file_path'   => $filePath,
                    'page_number' => 1,
                    'file_type'   => $fileType,
                    'mime_type'   => $mimeType,
                    'size_bytes'  => $fileSize,
                ]);
            }

            // Save sub-files / pages
            $pageNumber = 1;
            if ($hasMultiFiles) {
                foreach ($request->file('files') as $fileItem) {
                    $pagePath = ($pageNumber === 1) ? $filePath : $this->storageService->store($fileItem, $company, 'pages');
                    DocumentFile::create([
                        'document_id' => $document->id,
                        'file_path'   => $pagePath,
                        'page_number' => $pageNumber++,
                        'file_type'   => str_contains($fileItem->getMimeType(), 'pdf') ? 'pdf' : 'image',
                        'mime_type'   => $fileItem->getMimeType(),
                        'size_bytes'  => $fileItem->getSize(),
                    ]);
                }
            } elseif ($request->hasFile('pages')) {
                DocumentFile::create([
                    'document_id' => $document->id,
                    'file_path'   => $filePath,
                    'page_number' => $pageNumber++,
                    'file_type'   => $fileType,
                    'mime_type'   => $mimeType,
                    'size_bytes'  => $fileSize,
                ]);

                foreach ($request->file('pages') as $pageFile) {
                    $pagePath = $this->storageService->store($pageFile, $company, 'pages');
                    DocumentFile::create([
                        'document_id' => $document->id,
                        'file_path'   => $pagePath,
                        'page_number' => $pageNumber++,
                        'file_type'   => str_contains($pageFile->getMimeType(), 'pdf') ? 'pdf' : 'image',
                        'mime_type'   => $pageFile->getMimeType(),
                        'size_bytes'  => $pageFile->getSize(),
                    ]);
                }
            }

            $company->incrementStorage($totalBytes);
            ActivityLog::log('uploaded', $document, "Uploaded document '{$document->title}'");

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully.',
                'data'    => new DocumentResource($document->load(['user', 'folder', 'files'])),
            ], 201);
        });
    }

    public function show(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $document->load(['user', 'folder', 'files']);

        return response()->json([
            'success' => true,
            'data'    => new DocumentResource($document),
        ]);
    }

    public function update(UpdateDocumentRequest $request, Document $document): JsonResponse
    {
        $this->authorize('update', $document);

        $oldTitle = $document->title;
        $document->update($request->validated());

        if ($request->filled('title') && $request->title !== $oldTitle) {
            ActivityLog::log('renamed', $document, "Renamed document from '{$oldTitle}' to '{$document->title}'");
        } else {
            ActivityLog::log('updated', $document, "Updated document '{$document->title}'");
        }

        return response()->json([
            'success' => true,
            'message' => 'Document updated successfully.',
            'data'    => new DocumentResource($document->load(['user', 'folder', 'files'])),
        ]);
    }

    public function destroy(Document $document): JsonResponse
    {
        $this->authorize('delete', $document);

        $company = auth()->user()->company;
        $totalBytes = $document->size_bytes;

        foreach ($document->files as $file) {
            $this->storageService->delete($file->file_path);
        }

        $this->storageService->delete($document->file_path);
        if ($document->thumbnail_path) {
            $this->storageService->delete($document->thumbnail_path);
        }

        $title = $document->title;
        $document->delete();

        $company->decrementStorage($totalBytes);
        ActivityLog::log('deleted', null, "Deleted document '{$title}'");

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully.',
        ]);
    }

    public function download(Request $request, Document $document)
    {
        $this->authorize('download', $document);

        ActivityLog::log('downloaded', $document, "Downloaded document '{$document->title}'");

        // If explicitly requesting JSON or called from API with Json header
        if ($request->wantsJson() && !$request->hasHeader('X-Direct-Download')) {
            $url = $this->storageService->getUrl($document->file_path);
            return response()->json([
                'success'      => true,
                'download_url' => $url,
                'file_name'    => $document->title . '.' . pathinfo($document->file_path, PATHINFO_EXTENSION),
                'mime_type'    => $document->mime_type,
                'size_bytes'   => $document->size_bytes,
            ]);
        }

        // Direct file stream / download for browser requests
        if (Storage::exists($document->file_path)) {
            $filename = $document->title . '.' . pathinfo($document->file_path, PATHINFO_EXTENSION);
            return Storage::download($document->file_path, $filename);
        }

        $url = $this->storageService->getUrl($document->file_path);
        return response()->json([
            'success'      => true,
            'download_url' => $url,
            'file_name'    => $document->title . '.' . pathinfo($document->file_path, PATHINFO_EXTENSION),
            'mime_type'    => $document->mime_type,
            'size_bytes'   => $document->size_bytes,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:1']);

        $query = $request->q;
        $documents = Document::with(['user', 'folder'])
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->latest()
            ->limit(30)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => DocumentResource::collection($documents),
        ]);
    }
}
