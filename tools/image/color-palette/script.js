document.addEventListener('DOMContentLoaded', () => {
    const uploader = new Uploader({
        zone: '#upload-zone', fileInput: '#file-input', fileList: '#file-list',
        accepts: ['image/jpeg', 'image/png', 'image/webp'], maxFiles: 1, supportsPaste: true,
        onFilesChanged: (files) => {
            if (files.length > 0) {
                document.getElementById('action-buttons').style.display = 'flex';
                document.getElementById('btn-process').click();
            } else {
                document.getElementById('action-buttons').style.display = 'none';
            }
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
        btn.disabled = true; btn.innerHTML = '<span class="spinner"></span> Обработка...';

        const fd = new FormData();
        fd.append('file', files[0]);
        fd.append('_token', getCsrfToken());

        try {
            const r = await fetch('/api/tool/image-color-palette/process', { method: 'POST', body: fd, headers: {'X-CSRF-TOKEN': getCsrfToken()} });
            const data = await r.json();

            if (data.success) {
                let html = '';
                data.colors.forEach(hex => {
                    html += `
                    <div style="display:flex; align-items:center; gap:16px; background:var(--bg-card); padding:12px; border-radius:8px; border:1px solid var(--border-color);">
                        <div style="width:50px; height:50px; border-radius:8px; background-color:${hex}; box-shadow:0 2px 4px rgba(0,0,0,0.1);"></div>
                        <div style="flex:1; font-family:monospace; font-size:1.1rem; font-weight:bold;">${hex}</div>
                        <button class="btn btn-secondary" onclick="navigator.clipboard.writeText('${hex}'); showToast('Скопировано!', 'success')" style="padding:6px 12px; font-size:0.8rem;">📋 Копировать</button>
                    </div>`;
                });
                
                document.getElementById('palette-colors').innerHTML = html;
                document.getElementById('result-area').style.display = 'block';
                document.getElementById('empty-result-state').style.display = 'none';
            } else { showToast(data.error, 'error'); }
        } catch (e) {
            showToast('Ошибка сети', 'error');
        }

        btn.disabled = false; btn.innerHTML = '🎨 Получить палитру';
    });
});
