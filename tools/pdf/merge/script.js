document.addEventListener('DOMContentLoaded', () => {
    const uploader = new Uploader({
        zone: '#upload-zone', fileInput: '#file-input', fileList: '#file-list',
        accepts: ['application/pdf'], maxFiles: 20, supportsPaste: false, showPreview: false,
        onFilesChanged: (files) => {
            document.getElementById('action-buttons').style.display = files.length >= 2 ? 'flex' : 'none';
            if (files.length < 2) {
                document.getElementById('result-area').style.display = 'none';
                document.getElementById('empty-result-state').style.display = 'flex';
                document.getElementById('progress-wrap').classList.remove('active');
            }
        }
    });

    document.getElementById('btn-clear').addEventListener('click', () => uploader.clear());

    document.getElementById('btn-merge').addEventListener('click', async () => {
        const files = uploader.getFiles();
        if (files.length < 2) { showToast('Нужно минимум 2 PDF', 'error'); return; }

        const btn = document.getElementById('btn-merge');
        btn.disabled = true; btn.innerHTML = '<span class="spinner"></span> Объединение...';
        document.getElementById('progress-wrap').classList.add('active');
        document.getElementById('progress-bar').style.width = '50%';
        document.getElementById('result-area').style.display = 'none';
        document.getElementById('empty-result-state').style.display = 'flex';

        const fd = new FormData();
        files.forEach((f) => fd.append('files[]', f));
        fd.append('_token', getCsrfToken());

        try {
            const r = await fetch('/api/tool/pdf-merge/process', { method: 'POST', headers: {'X-CSRF-TOKEN': getCsrfToken()}, body: fd });
            const data = await r.json();
            document.getElementById('progress-bar').style.width = '100%';

            if (data.success) {
                document.getElementById('result-files').innerHTML = `
                    <div class="result-item">
                        <div><div class="result-item-name">merged.pdf</div><div class="result-item-info">${formatSize(data.size)} • ${data.pages} страниц</div></div>
                        <a href="#" onclick="forceDownload('${data.download_url}', 'merged.pdf'); return false;" class="btn btn-success" style="padding:8px 16px;font-size:0.85rem;">Скачать</a>
                    </div>`;
                document.getElementById('result-area').style.display = 'block';
                document.getElementById('empty-result-state').style.display = 'none';
            } else { showToast(data.error, 'error'); }
        } catch(e) { showToast('Ошибка сети', 'error'); }

        setTimeout(() => document.getElementById('progress-wrap').classList.remove('active'), 1000);
        btn.disabled = false; btn.innerHTML = '📑 Объединить PDF';
    });
});
