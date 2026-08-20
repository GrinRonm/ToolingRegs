document.addEventListener('DOMContentLoaded', () => {
    const uploader = new Uploader({
        zone: '#upload-zone', fileInput: '#file-input', fileList: '#file-list',
        accepts: ['application/pdf'], maxFiles: 1, supportsPaste: false, showPreview: false,
        onFilesChanged: (files) => {
            document.getElementById('action-buttons').style.display = files.length > 0 ? 'flex' : 'none';
        }
    });

    document.getElementById('btn-clear').addEventListener('click', () => {
        uploader.clear();
        document.getElementById('result-area').style.display = 'none';
        document.getElementById('empty-result-state').style.display = 'flex';
    });

    document.getElementById('btn-process').addEventListener('click', async () => {
        const files = uploader.getFiles();
        const pwd = document.getElementById('pdf-password').value;
        if (!files.length) return showToast('Добавьте PDF', 'error');
        if (!pwd) return showToast('Введите пароль', 'error');

        const btn = document.getElementById('btn-process');
        btn.disabled = true; btn.innerHTML = '<span class="spinner"></span> Шифрование...';
        document.getElementById('progress-wrap').classList.add('active');
        document.getElementById('progress-bar').style.width = '50%';
        document.getElementById('result-area').style.display = 'none';
        document.getElementById('empty-result-state').style.display = 'flex';

        const fd = new FormData();
        fd.append('file', files[0]);
        fd.append('password', pwd);
        fd.append('_token', getCsrfToken());

        try {
            const r = await fetch('/api/tool/pdf-password/process', { method: 'POST', headers: {'X-CSRF-TOKEN': getCsrfToken()}, body: fd });
            const data = await r.json();
            document.getElementById('progress-bar').style.width = '100%';

            if (data.success) {
                const html = `<div class="result-item"><div><div class="result-item-name">${data.file_name}</div></div><a href="#" onclick="forceDownload('${data.download_url}', '${data.file_name}'); return false;" class="btn btn-secondary" style="padding:8px 16px;font-size:0.85rem;">Скачать</a></div>`;
                document.getElementById('result-files').innerHTML = html;
                document.getElementById('result-area').style.display = 'block';
                document.getElementById('empty-result-state').style.display = 'none';
                
                document.getElementById('pdf-password').value = '';
            } else { showToast(data.error, 'error'); }
        } catch(e) { showToast('Ошибка сети', 'error'); }

        setTimeout(() => document.getElementById('progress-wrap').classList.remove('active'), 1000);
        btn.disabled = false; btn.innerHTML = '🔒 Зашифровать';
    });
});
