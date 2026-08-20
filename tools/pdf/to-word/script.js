document.addEventListener('DOMContentLoaded', () => {
    const uploader = new Uploader({
        zone: '#upload-zone', fileInput: '#file-input', fileList: '#file-list',
        accepts: ['application/pdf'], maxFiles: 5, supportsPaste: false, showPreview: false,
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
        if (!files.length) return;

        const btn = document.getElementById('btn-process');
        const progress = document.getElementById('progress-wrap');
        const bar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');

        btn.disabled = true; btn.innerHTML = '<span class="spinner"></span> Конвертация...';
        progress.classList.add('active');
        document.getElementById('result-area').style.display = 'none';
        document.getElementById('empty-result-state').style.display = 'flex';

        let downloadLinks = [];
        let html = '';

        for (let i = 0; i < files.length; i++) {
            bar.style.width = `${((i) / files.length) * 100}%`;
            progressText.textContent = `Конвертация ${i + 1} из ${files.length}...`;

            const fd = new FormData();
            fd.append('file', files[i]);
            fd.append('_token', getCsrfToken());

            try {
                const r = await fetch('/api/tool/pdf-to-word/process', { method: 'POST', body: fd, headers: {'X-CSRF-TOKEN': getCsrfToken()} });
                const data = await r.json();
                if (data.success) {
                    downloadLinks.push(data.download_url);
                    html += `<div class="result-item"><div><div class="result-item-name">${data.file_name}</div></div><a href="#" onclick="forceDownload('${data.download_url}', '${data.file_name}'); return false;" class="btn btn-secondary" style="padding:8px 16px;font-size:0.85rem;">Скачать</a></div>`;
                } else {
                    html += `<div class="result-item"><span class="result-item-name">❌ ${files[i].name}</span><span class="result-item-info">${data.error}</span></div>`;
                }
            } catch (e) {
                html += `<div class="result-item"><span class="result-item-name">❌ Ошибка сети</span></div>`;
            }
        }

        bar.style.width = '100%';
        progressText.textContent = 'Готово!';
        
        document.getElementById('result-files').innerHTML = html;
        document.getElementById('result-area').style.display = 'block';
        document.getElementById('empty-result-state').style.display = 'none';

        if (downloadLinks.length > 1) {
            const btnAll = document.getElementById('btn-download-all');
            btnAll.style.display = 'block';
            btnAll.onclick = () => downloadAsZip(downloadLinks);
        }

        setTimeout(() => progress.classList.remove('active'), 1000);
        btn.disabled = false; btn.innerHTML = '📄 Конвертировать в Word';
    });
});
