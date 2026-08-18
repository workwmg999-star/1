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
use Illuminate\Support\Facades\Storage;

class DocumentWebController extends Controller
{
    public function __construct(
        private DocumentStorageService $storageService,
        private SubscriptionLimitService $limitService
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user    = Auth::user();
        $company = $user->company;

        $query = Document::where('company_id', $company->id)
            ->with(['folder', 'user'])
            ->latest();

        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        }

        if ($request->filled('file_type')) {
            $query->where('file_type', $request->file_type);
        }

        $documents = $query->paginate(15);

        $folders = Folder::where('company_id', $company->id)
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
            'currentFolder' => $currentFolder,
            'user'          => $user->toArray(),
        ]);
    }

    public function scan(Request $request)
    {
        $user = Auth::user()->load('company.plan');

        $folders = Folder::where('company_id', $user->company_id)
            ->orderBy('name')
            ->get()
            ->toArray();

        return view('scan', [
            'folders' => $folders,
            'user'    => $user->toArray(),
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

        if ($request->filled('folder_id')) {
            $folderBelongsToCompany = Folder::where('id', $request->folder_id)
                ->where('company_id', $company->id)
                ->exists();

            if (!$folderBelongsToCompany) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The selected folder is invalid.'
                    ], 422);
                }
                return back()->with('error', 'The selected folder is invalid.');
            }
        }

        $file     = $request->file('file');
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();
        $fileType = str_contains($mimeType, 'pdf') ? 'pdf' : 'image';

        if (!$this->limitService->canUploadFile($company, $fileSize)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Storage limit exceeded. Please upgrade.'
                ], 402);
            }
            return back()->with('error', 'Storage limit exceeded.');
        }

        if (!$this->limitService->canAddDocument($company)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document limit reached. Please upgrade.'
                ], 402);
            }
            return back()->with('error', 'Document limit reached.');
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

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Document \"{$doc->title}\" saved successfully!",
                'data'    => [
                    'id'        => $doc->id,
                    'title'     => $doc->title,
                    'folder_id' => $doc->folder_id
                ],
            ], 201);
        }

        return redirect()->route('documents.index')->with('success', "Document \"{$doc->title}\" saved successfully!");
    }

    public function download($id)
    {
        $doc = Document::where('company_id', Auth::user()->company_id)->findOrFail($id);

        if (Storage::disk('local')->exists($doc->file_path)) {
            return Storage::disk('local')->download($doc->file_path, $doc->title . '.' . pathinfo($doc->file_path, PATHINFO_EXTENSION));
        }

        return back()->with('error', 'File not found in cloud storage.');
    }

    public function destroy($id)
    {
        $doc = Document::where('company_id', Auth::user()->company_id)->findOrFail($id);

        $size = $doc->size_bytes;
        $company = Auth::user()->company;

        $doc->delete();
        $company->decrementStorage($size);

        return back()->with('success', 'Document deleted successfully.');
    }

    public function search(Request $request)
    {
        $q = $request->get('q', '');
        $company = Auth::user()->company;

        $documents = Document::where('company_id', $company->id)
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
            })
            ->with('folder')
            ->latest()
            ->get()
            ->toArray();

        return view('documents.search', [
            'q'         => $q,
            'documents' => $documents,
        ]);
    }
}