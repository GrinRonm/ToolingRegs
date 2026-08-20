document.addEventListener('DOMContentLoaded', () => {
    const uploader = new Uploader({
        zone: '#upload-zone', fileInput: '#file-input', fileList: '#file-list',
        accepts: ['application/pdf'], maxFiles: 1, supportsPaste: false, showPreview: false,
        onFilesChanged: (files) => {
            const h = files.length > 0;
            document.getElementById('options-panel').style.display = h ? '' : 'none';
            document.getElementById('action-buttons').style.display = h ? 'flex' : 'none';
            if (!h) {
                document.getElementById('result-area').style.display = 'none';
                document.getElementById('empty-result-state').style.display = 'flex';
                document.getElementById('progress-wrap').classList.remove('active');
            }
        }
    });
    document.getElementById('btn-clear').addEventListener('click', () => uploader.clear());
    
    document.getElementById('btn-convert').addEventListener('click', async () => {
        const files = uploader.getFiles();
        if (!files.length) return;

        const format = document.getElementById('image-format').value;
        const btn = document.getElementById('btn-convert');
        btn.disabled = true; btn.innerHTML = '<span class="spinner"></span> Конвертация...';
        document.getElementById('progress-wrap').classList.add('active');
        document.getElementById('progress-bar').style.width = '50%';
        document.getElementById('result-area').style.display = 'none';
        document.getElementById('empty-result-state').style.display = 'flex';

        const fd = new FormData();
        fd.append('file', files[0]); fd.append('format', format); fd.append('_token', getCsrfToken());

        try {
            const r = await fetch('/api/tool/pdf-to-images/process', { method: 'POST', headers: {'X-CSRF-TOKEN': getCsrfToken()}, body: fd });
            const data = await r.json();
            document.getElementById('progress-bar').style.width = '100%';

            if (data.success) {
                document.getElementById('result-stats').textContent = `${data.pages} страниц`;
                let html = '';
                data.files.forEach(f => {
                    html += `<div class="result-item"><div><div class="result-item-name">Страница ${f.page}.${format}</div><div class="result-item-info">${formatSize(f.size)}</div></div><a href="#" onclick="forceDownload('${f.download_url}', 'page_${f.page}.${format}'); return false;" class="btn btn-secondary" style="padding:8px 16px;font-size:0.85rem;">Скачать</a></div>`;
                });
                document.getElementById('result-files').innerHTML = html;
                document.getElementById('result-area').style.display = 'block';
                document.getElementById('empty-result-state').style.display = 'none';
                
                if (data.files.length > 1) {
                    const btnAll = document.getElementById('btn-download-all');
                    btnAll.style.display = 'block';
                    btnAll.onclick = () => downloadAsZip(data.files.map(f => f.download_url));
                }
            } else { showToast(data.error, 'error'); }
        } catch(e) { showToast('Ошибка сети', 'error'); }
        
        setTimeout(() => document.getElementById('progress-wrap').classList.remove('active'), 1000);
        btn.disabled = false; btn.innerHTML = '🖼️ Конвертировать';
    });
});
