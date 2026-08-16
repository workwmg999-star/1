<div class="modal-overlay" id="uploadModal">
    <div class="modal" style="max-width:520px;">
        <div class="modal-title">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="22" height="22" style="display:inline;vertical-align:middle;margin-right:8px;color:var(--primary)"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            Upload Scanned Document
        </div>

        <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label class="form-label">Document Title <span style="color:var(--danger)">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Declaration Douane Port Casa #2026" required>
            </div>

            <div class="form-group">
                <label class="form-label">Folder</label>
                <select name="folder_id" class="form-control">
                    <option value="">— Unfiled (Root) —</option>
                    @foreach(session('folders', []) as $folder)
                    <option value="{{ $folder['id'] }}">{{ $folder['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">File (PDF or Image) <span style="color:var(--danger)">*</span></label>
                <div class="file-drop" id="fileDrop" onclick="document.getElementById('fileInput').click()">
                    <div class="file-drop-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="48" height="48"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    </div>
                    <div class="file-drop-text">
                        <span>Click to browse</span> or drag & drop<br>
                        <small style="color:var(--text-light)">PDF, JPG, PNG, WEBP — Max 50 MB</small>
                    </div>
                    <div id="selectedFileName" style="margin-top:10px;font-size:13px;color:var(--primary);font-weight:600;display:none;"></div>
                </div>
                <input type="file" id="fileInput" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp" style="display:none" required>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Optional description..."></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('uploadModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    Upload Document
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const fileInput = document.getElementById('fileInput');
const fileDrop  = document.getElementById('fileDrop');
const nameLabel = document.getElementById('selectedFileName');

fileInput?.addEventListener('change', function() {
    if (this.files[0]) {
        nameLabel.textContent = '📎 ' + this.files[0].name;
        nameLabel.style.display = 'block';
    }
});

fileDrop?.addEventListener('dragover', e => { e.preventDefault(); fileDrop.classList.add('dragover'); });
fileDrop?.addEventListener('dragleave',  () => fileDrop.classList.remove('dragover'));
fileDrop?.addEventListener('drop', e => {
    e.preventDefault(); fileDrop.classList.remove('dragover');
    const dt = new DataTransfer();
    dt.items.add(e.dataTransfer.files[0]);
    fileInput.files = dt.files;
    nameLabel.textContent = '📎 ' + e.dataTransfer.files[0].name;
    nameLabel.style.display = 'block';
});
</script>
@endpush
