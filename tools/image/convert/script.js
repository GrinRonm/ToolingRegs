document.addEventListener('DOMContentLoaded', () => {
    const uploader = new Uploader({
        zone: '#upload-zone',
        fileInput: '#file-input',
        fileList: '#file-list',
        accepts: ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp'],
        maxFiles: 20,
        supportsPaste: true,
        onFilesChanged: (files) => {
            const hasFiles = files.length > 0;
            document.getElementById('options-panel').style.display = hasFiles ? '' : 'none';
            document.getElementById('action-buttons').style.display = hasFiles ? 'flex' : 'none';
            if (!hasFiles) {
                document.getElementById('result-area').style.display = 'none';
                document.getElementById('empty-result-state').style.display = 'flex';
                document.getElementById('progress-wrap').classList.remove('active');
            }
        }
    });

    const slider = document.getElementById('quality-slider');
    const valueDisplay = document.getElementById('quality-value');
    slider.addEventListener('input', () => { valueDisplay.textContent = slider.value + '%'; });

    const formatSelect = document.getElementById('target-format');
    const qualityRow = document.getElementById('quality-row');
    formatSelect.addEventListener('change', () => {
        const format = formatSelect.value;
        qualityRow.style.display = ['jpg', 'webp'].includes(format) ? '' : 'none';
    });

    document.getElementById('btn-clear').addEventListener('click', () => uploader.clear());

    document.getElementById('btn-convert').addEventListener('click', async () => {
        const files = uploader.getFiles();
        if (files.length === 0) return;

        const targetFormat = formatSelect.value;
        const quality = parseInt(slider.value);
        const btn = document.getElementById('btn-convert');
        const progress = document.getElementById('progress-wrap');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Конвертация...';
        progress.classList.add('active');
        document.getElementById('result-area').style.display = 'none';
        document.getElementById('empty-result-state').style.display = 'flex';

        const results = [];
        const downloadLinks = [];

        for (let i = 0; i < files.length; i++) {
            progressBar.style.width = Math.round((i / files.length) * 100) + '%';
            progressText.textContent = `Конвертация ${i + 1} из ${files.length}...`;

            const formData = new FormData();
            formData.append('file', files[i]);
            formData.append('format', targetFormat);
            formData.append('quality', quality);
            formData.append('_token', getCsrfToken());

            try {
                const resp = await fetch('/api/tool/image-convert/process', { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrfToken() }, body: formData });
                const data = await resp.json();
                if (data.success) { results.push(data); downloadLinks.push(data.download_url); } 
                else { results.push({ error: data.error, name: files[i].name }); }
            } catch (err) { results.push({ error: 'Ошибка сети', name: files[i].name }); }
        }

        progressBar.style.width = '100%';
        let filesHtml = '';
        results.forEach(r => {
            if (r.error) {
                filesHtml += `<div class="result-item"><span class="result-item-name">❌ ${r.name}</span><span class="result-item-info">${r.error}</span></div>`;
            } else {
                filesHtml += `<div class="result-item"><div><div class="result-item-name">${r.original_name} → .${r.target_format}</div><div class="result-item-info">${formatSize(r.new_size)}</div></div><a href="#" onclick="forceDownload('${r.download_url}', '${r.original_name.split('.')[0]}.${r.target_format}'); return false;" class="btn btn-secondary" style="padding:8px 16px;font-size:0.85rem;">Скачать</a></div>`;
            }
        });

        document.getElementById('result-files').innerHTML = filesHtml;
        document.getElementById('result-area').style.display = 'block';
        document.getElementById('empty-result-state').style.display = 'none';

        if (downloadLinks.length > 1) {
            const btnAll = document.getElementById('btn-download-all');
            btnAll.style.display = '';
            btnAll.onclick = () => downloadAsZip(downloadLinks);
        }

        setTimeout(() => progress.classList.remove('active'), 1500);
        btn.disabled = false;
        btn.innerHTML = '🔄 Конвертировать';
    });
});
