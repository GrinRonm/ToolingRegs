document.addEventListener('DOMContentLoaded', () => {
    const uploader = new Uploader({
        zone: '#upload-zone', fileInput: '#file-input', fileList: '#file-list',
        accepts: ['application/pdf'], maxFiles: 1, supportsPaste: false, showPreview: false,
        onFilesChanged: (files) => {
            document.getElementById('action-buttons').style.display = files.length > 0 ? 'flex' : 'none';
            if (files.length === 0) {
                document.getElementById('result-area').style.display = 'none';
                document.getElementById('empty-result-state').style.display = 'flex';
                document.getElementById('progress-wrap').classList.remove('active');
            }
        }
    });
    document.getElementById('btn-clear').addEventListener('click', () => uploader.clear());
    document.getElementById('btn-compress').addEventListener('click', async () => {
        const files = uploader.getFiles();
        if (!files.length) return;
        
        const btn = document.getElementById('btn-compress');
        btn.disabled = true; btn.innerHTML = '<span class="spinner"></span> Сжатие...';
        document.getElementById('progress-wrap').classList.add('active');
        document.getElementById('progress-bar').style.width = '50%';
        document.getElementById('result-area').style.display = 'none';
        document.getElementById('empty-result-state').style.display = 'flex';

        const fd = new FormData();
        fd.append('file', files[0]); fd.append('_token', getCsrfToken());
        
        try {
            const r = await fetch('/api/tool/pdf-compress/process', { method: 'POST', headers: {'X-CSRF-TOKEN': getCsrfToken()}, body: fd });
            const data = await r.json();
            document.getElementById('progress-bar').style.width = '100%';
            if (data.success) {
                const saved = Math.round((1 - data.compressed_size / data.original_size) * 100);
                document.getElementById('result-stats').innerHTML = `<p>${formatSize(data.original_size)} → ${formatSize(data.compressed_size)} (−${saved}%)</p>`;
                document.getElementById('result-files').innerHTML = `<div class="result-item"><div><div class="result-item-name">compressed.pdf</div><div class="result-item-info">${data.pages} стр.</div></div><a href="#" onclick="forceDownload('${data.download_url}', 'compressed.pdf'); return false;" class="btn btn-success" style="padding:8px 16px;font-size:0.85rem;">Скачать</a></div>`;
                document.getElementById('result-area').style.display = 'block';
                document.getElementById('empty-result-state').style.display = 'none';
            } else { showToast(data.error, 'error'); }
        } catch(e) { showToast('Ошибка сети', 'error'); }
        
        setTimeout(() => document.getElementById('progress-wrap').classList.remove('active'), 1000);
        btn.disabled = false; btn.innerHTML = '📦 Сжать PDF';
    });
});
