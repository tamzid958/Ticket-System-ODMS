<div class="p-4">
    @if(count($files) > 0)
        <div class="file-list-container">
            <div class="file-list-header">
                <div class="file-list-cell" style="flex: 3;">Name</div>
                <div class="file-list-cell" style="flex: 1;">Type</div>
                <div class="file-list-cell" style="flex: 1;">Size</div>
                <div class="file-list-cell" style="flex: 1; text-align: center;">Actions</div>
            </div>

            <div class="file-list-body">
                @foreach($files as $file)
                    @php
                        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                        if (in_array(strtolower($extension), ['jpg','jpeg','png','gif','bmp','webp','svg'])) {
                            $icon = 'fas fa-image';
                            $type = 'Image';
                            $color = 'text-green-600';
                        } elseif (in_array(strtolower($extension), ['pdf'])) {
                            $icon = 'fas fa-file-pdf';
                            $type = 'PDF';
                            $color = 'text-red-600';
                        } elseif (in_array(strtolower($extension), ['doc','docx'])) {
                            $icon = 'fas fa-file-word';
                            $type = 'Word';
                            $color = 'text-blue-600';
                        } elseif (in_array(strtolower($extension), ['xls','xlsx','csv'])) {
                            $icon = 'fas fa-file-excel';
                            $type = 'Spreadsheet';
                            $color = 'text-green-700';
                        } elseif (in_array(strtolower($extension), ['zip','rar','7z'])) {
                            $icon = 'fas fa-file-archive';
                            $type = 'Archive';
                            $color = 'text-yellow-700';
                        } elseif (in_array(strtolower($extension), ['mp4','mov','avi','mkv'])) {
                            $icon = 'fas fa-file-video';
                            $type = 'Video';
                            $color = 'text-purple-600';
                        } elseif (in_array(strtolower($extension), ['js','css','html','php','py','java'])) {
                            $icon = 'fas fa-file-code';
                            $type = 'Code';
                            $color = 'text-pink-600';
                        } else {
                            $icon = 'fas fa-file';
                            $type = 'File';
                            $color = 'text-gray-500';
                        }
                    @endphp

                    <div class="file-list-row" onclick="window.open('{{ $file['url'] }}', '_blank')" style="cursor:pointer;">
                        <div class="file-list-cell" style="flex: 3; display:flex; align-items:center;">
                            <i class="{{ $icon }} {{ $color }} mr-3 w-5 text-center"></i>
                            <span class="file-name" title="{{ $file['name'] }}">{{ $file['name'] }}</span>
                        </div>

                        <div class="file-list-cell" style="flex: 1;">
                            <span class="file-type">{{ $type }}</span>
                        </div>

                        <div class="file-list-cell" style="flex: 1;">
                            <span class="file-size">{{ round($file['size'] / 1024, 1) }} KB</span>
                        </div>

                        <div class="file-list-cell" style="flex: 1; text-align: center;">
                            <a href="{{ $file['url'] }}" download class="download-btn"
                            onclick="event.stopPropagation()">
                                <i class="fas fa-download mr-1"></i> Download
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="text-center py-8 text-gray-600">
            <i class="fas fa-folder-open text-4xl mb-3 text-gray-400"></i>
            <p class="text-gray-500">No attachments found</p>
        </div>
    @endif
</div>

<!-- Preview Modal -->
<div id="previewModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
    background:rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index:1000;">
    <div style="position:relative; max-width:90%; max-height:90%; background:#fff; padding:16px; border-radius:8px;">
        <span onclick="closeModal()" style="position:absolute; top:8px; right:12px; cursor:pointer; font-size:20px;">&times;</span>
        <div id="previewContent" style="width:100%; height:100%; display:flex; justify-content:center; align-items:center;"></div>
    </div>
</div>

<script>
function previewFile(url, type) {
    let content = '';
    if(type === 'Image') {
        content = '<img src="'+url+'" style="max-width:100%; max-height:100%; border-radius:6px;">';
    } else if(type === 'PDF') {
        content = '<iframe src="'+url+'" style="width:100%; height:80vh;" frameborder="0"></iframe>';
    } else if(type === 'Video') {
        content = '<video controls style="max-width:100%; max-height:80vh;"><source src="'+url+'"></video>';
    } else {
        content = '<p style="color:#333;">Preview not available for this file type.</p>';
    }
    document.getElementById('previewContent').innerHTML = content;
    document.getElementById('previewModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('previewModal').style.display = 'none';
    document.getElementById('previewContent').innerHTML = '';
}
</script>

<style>
.file-list-container {
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.file-list-header {
    display: flex;
    background: #64748b;
    color: white;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 14px;
    border-bottom: 1px solid #cbd5e1;
}

.file-list-body {
    max-height: 400px;
    overflow-y: auto;
    background: #f8fafc;
    padding: 4px 0;
}

.file-list-row {
    display: flex;
    padding: 12px 16px;
    border-radius: 6px;
    background: #ffffff;
    margin: 4px 12px;
    align-items: center;
    transition: background 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.file-list-row:hover {
    background: #eef6ff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.file-list-row:nth-child(even) {
    background: #f9fafb;
}

.file-list-row:nth-child(even):hover {
    background: #e6f0ff;
}

.file-list-cell {
    padding: 0 10px;
}

.file-name {
    font-size: 14px;
    color: #374151;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 500;
}

.file-type {
    font-size: 13px;
    color: #475569;
    background: #f1f5f9;
    padding: 3px 8px;
    border-radius: 12px;
    display: inline-block;
}

.file-size {
    font-size: 13px;
    color: #475569;
    font-weight: 500;
}

.download-btn {
    background: #3b82f6;
    color: white;
    border-radius: 4px;
    padding: 6px 12px;
    font-size: 12px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: background 0.2s ease, transform 0.2s ease;
}

.download-btn:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

/* Scrollbar styling */
.file-list-body::-webkit-scrollbar {
    width: 6px;
}
.file-list-body::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}
.file-list-body::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.file-list-body::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
