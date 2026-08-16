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
                <input type="text" name="title" class="form-control" placeholder="e.g. Declaration Douane #2026" required>
            </div>
            <div class="form-group">
                <label class="form-label">Folder</label>
                <select name="folder_id" class="form-control">
                    <option value="">— Unfiled —</option>
                    @foreach($folders as $folder)
                    <option value="{{ $folder['id'] }}" {{ request('folder_id') == $folder['id'] ? 'selected' : '' }}>{{ $folder['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">File <span style="color:var(--danger)">*</span></label>
                <div class="file-drop" id="fileDrop2" onclick="document.getElementById('fileInput2').click()">
                    <div class="file-drop-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="48" height="48"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    </div>
                    <div class="file-drop-text">
                        <span>Click to browse</span> or drag & drop<br>
                        <small style="color:var(--text-light)">PDF, JPG, PNG, WEBP — Max 50 MB</small>
                    </div>
                    <div id="selectedFileName2" style="margin-top:10px;font-size:13px;color:var(--primary);font-weight:600;display:none;"></div>
                </div>
                <input type="file" id="fileInput2" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp" style="display:none" required>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Optional..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('uploadModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Upload Document</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
const fi2 = document.getElementById('fileInput2');
const fd2 = document.getElementById('fileDrop2');
const fn2 = document.getElementById('selectedFileName2');
fi2?.addEventListener('change', function() { if(this.files[0]){fn2.textContent='📎 '+this.files[0].name;fn2.style.display='block';} });
fd2?.addEventListener('dragover', e=>{e.preventDefault();fd2.classList.add('dragover');});
fd2?.addEventListener('dragleave',()=>fd2.classList.remove('dragover'));
fd2?.addEventListener('drop', e=>{e.preventDefault();fd2.classList.remove('dragover');const dt=new DataTransfer();dt.items.add(e.dataTransfer.files[0]);fi2.files=dt.files;fn2.textContent='📎 '+e.dataTransfer.files[0].name;fn2.style.display='block';});
</script>
@endpush
