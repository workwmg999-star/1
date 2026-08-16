<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\Folder;
use App\Services\DocumentStorageService;
use App\Services\SubscriptionLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocumentWebController extends Controller
{
    public function __construct(
        private DocumentStorageService $storageService,
        private SubscriptionLimitService $limitService
    ) {}

    public function scan(Request $request)
    {
        $user = Auth::user()->load('company.plan');
        $folders = Folder::orderBy('name')->get()->toArray();

        return view('scan', [
            'folders' => $folders,
            'user'    => $user->toArray(),
        ]);
    }

    public function index(Request $request)
    {
        $user = Auth::user()->load('company.plan');
        $query = Document::with(['user', 'folder'])->latest();

        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        }

        if ($request->filled('file_type')) {
            $query->where('file_type', $request->file_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $documents = $query->paginate(15);

        $folders = Folder::withCount('documents')
            ->orderBy('name')
            ->get()
            ->toArray();

        $currentFolder = null;
        if ($request->filled('folder_id')) {
            $currentFolder = Folder::find($request->folder_id)?->toArray();
        }

        return view('documents.index', [
            'documents'     => $documents->items(),
            'meta'          => [
                'current_page' => $documents->currentPage(),
                'last_page'    => $documents->lastPage(),
                'total'        => $documents->total(),
            ],
            'folders'       => $folders,
            'user'          => $user->toArray(),
            'currentFolder' => $currentFolder,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'file'        => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:51200',
            'folder_id'   => 'nullable|integer|exists:folders,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $user    = Auth::user();
        $company = $user->company;

        $file     = $request->file('file');
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();
        $fileType = str_contains($mimeType, 'pdf') ? 'pdf' : 'image';

        if (!$this->limitService->canUploadFile($company, $fileSize)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Storage limit exceeded. Please upgrade your subscription plan.'], 402);
            }
            return back()->with('error', 'Storage limit exceeded. Please upgrade your subscription plan.');
        }

        if (!$this->limitService->canAddDocument($company)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Document count limit reached. Please upgrade your subscription plan.'], 402);
            }
            return back()->with('error', 'Document count limit reached. Please upgrade your subscription plan.');
        }

        $doc = DB::transaction(function () use ($request, $user, $company, $file, $fileSize, $mimeType, $fileType) {
            $filePath = $this->storageService->store($file, $company, 'documents');

            $document = Document::create([
                'company_id'  => $company->id,
                'user_id'     => $user->id,
                'folder_id'   => $request->folder_id ?: null,
                'title'       => $request->title,
                'description' => $request->description,
                'file_path'   => $filePath,
                'file_type'   => $fileType,
                'mime_type'   => $mimeType,
                'size_bytes'  => $fileSize,
                'pages_count' => 1,
            ]);

            $company->incrementStorage($fileSize);
            ActivityLog::log('uploaded', $document, "Uploaded document '{$document->title}'");
            return $document;
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Document \"{$request->title}\" uploaded successfully!",
                'data'    => ['id' => $doc->id, 'title' => $doc->title],
            ], 201);
        }

        return back()->with('success', "Document \"{$request->title}\" uploaded successfully!");
    }

    public function destroy(int $id)
    {
        $document = Document::findOrFail($id);
        $company  = Auth::user()->company;

        $totalBytes = $document->size_bytes;
        $this->storageService->delete($document->file_path);

        $document->delete();
        $company->decrementStorage($totalBytes);

        return back()->with('success', 'Document deleted successfully.');
    }

    public function download(int $id)
    {
        $document = Document::findOrFail($id);
        $url      = $this->storageService->getUrl($document->file_path);

        return redirect($url);
    }

    public function search(Request $request)
    {
        $q = $request->q ?? '';
        $user = Auth::user()->load('company.plan');

        $documents = Document::with(['user', 'folder'])
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
            })
            ->latest()
            ->limit(30)
            ->get()
            ->toArray();

        $folders = Folder::orderBy('name')->get()->toArray();

        return view('documents.search', [
            'documents' => $documents,
            'q'         => $q,
            'folders'   => $folders,
            'user'      => $user->toArray(),
        ]);
    }
}
