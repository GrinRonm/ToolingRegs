document.addEventListener('DOMContentLoaded', () => {
    const uploader = new Uploader({
        zone: '#upload-zone', fileInput: '#file-input', fileList: '#file-list',
        accepts: ['image/jpeg', 'image/png', 'image/webp'], maxFiles: 10, supportsPaste: true,
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

    document.getElementById('btn-resize').addEventListener('click', async () => {
        const files = uploader.getFiles();
        if (!files.length) return;

        const width = parseInt(document.getElementById('resize-width').value) || 0;
        const height = parseInt(document.getElementById('resize-height').value) || 0;
        const keepAspect = document.getElementById('keep-aspect').checked;

        if (width <= 0 && height <= 0) { showToast('Укажите ширину или высоту', 'error'); return; }

        const btn = document.getElementById('btn-resize');
        btn.disabled = true; btn.innerHTML = '<span class="spinner"></span> Ресайз...';
        document.getElementById('progress-wrap').classList.add('active');
        document.getElementById('result-area').style.display = 'none';
        document.getElementById('empty-result-state').style.display = 'flex';

        const results = [];
        for (let i = 0; i < files.length; i++) {
            document.getElementById('progress-bar').style.width = Math.round((i/files.length)*100)+'%';
            document.getElementById('progress-text').textContent = `${i+1} из ${files.length}...`;

            const fd = new FormData();
            fd.append('file', files[i]); fd.append('width', width); fd.append('height', height);
            fd.append('keep_aspect', keepAspect ? '1' : '0'); fd.append('_token', getCsrfToken());

            try {
                const r = await fetch('/api/tool/image-resize/process', { method: 'POST', headers: {'X-CSRF-TOKEN': getCsrfToken()}, body: fd });
                results.push(await r.json());
            } catch(e) { results.push({error:'Ошибка сети',name:files[i].name}); }
        }

        document.getElementById('progress-bar').style.width = '100%';
        let html = '';
        results.forEach(r => {
            if (r.error) {
                html += `<div class="result-item"><span>❌ ${r.name||''}</span><span class="result-item-info">${r.error}</span></div>`;
            } else {
                html += `<div class="result-item"><div><div class="result-item-name">${r.original_name}</div><div class="result-item-info">${r.original_dims} → ${r.new_dims}</div></div><a href="#" onclick="forceDownload('${r.download_url}', '${r.file_name}'); return false;" class="btn btn-secondary" style="padding:8px 16px;font-size:0.85rem;">Скачать</a></div>`;
            }
        });
        document.getElementById('result-files').innerHTML = html;
        document.getElementById('result-area').style.display = 'block';
        document.getElementById('empty-result-state').style.display = 'none';
        
        setTimeout(() => document.getElementById('progress-wrap').classList.remove('active'), 1000);
        btn.disabled = false; btn.innerHTML = '📐 Изменить размер';
    });
});
