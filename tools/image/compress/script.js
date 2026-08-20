document.addEventListener('DOMContentLoaded', () => {
    const uploader = new Uploader({
        zone: '#upload-zone',
        fileInput: '#file-input',
        fileList: '#file-list',
        accepts: ['image/jpeg', 'image/png', 'image/webp'],
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

    // Quality slider
    const slider = document.getElementById('quality-slider');
    const valueDisplay = document.getElementById('quality-value');
    slider.addEventListener('input', () => {
        valueDisplay.textContent = slider.value + '%';
    });

    // Clear button
    document.getElementById('btn-clear').addEventListener('click', () => {
        uploader.clear();
    });

    // Compress button
    document.getElementById('btn-compress').addEventListener('click', async () => {
        const files = uploader.getFiles();
        if (files.length === 0) return;

        const quality = parseInt(slider.value);
        const btn = document.getElementById('btn-compress');
        const progress = document.getElementById('progress-wrap');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const resultArea = document.getElementById('result-area');
        const resultStats = document.getElementById('result-stats');
        const resultFiles = document.getElementById('result-files');
        const downloadAllBtn = document.getElementById('btn-download-all');

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Сжатие...';
        progress.classList.add('active');
        resultArea.classList.remove('active');

        const results = [];
        let totalOriginal = 0;
        let totalCompressed = 0;

        for (let i = 0; i < files.length; i++) {
            const pct = Math.round(((i) / files.length) * 100);
            progressBar.style.width = pct + '%';
            progressText.textContent = `Обработка ${i + 1} из ${files.length}...`;

            const formData = new FormData();
            formData.append('file', files[i]);
            formData.append('quality', quality);
            formData.append('_token', getCsrfToken());

            try {
                const resp = await fetch('/api/tool/image-compress/process', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                    body: formData
                });
                const data = await resp.json();

                if (data.success) {
                    totalOriginal += data.original_size;
                    totalCompressed += data.compressed_size;
                    results.push(data);
                } else {
                    results.push({ error: data.error, name: files[i].name });
                }
            } catch (err) {
                results.push({ error: 'Ошибка сети', name: files[i].name });
            }
        }

        progressBar.style.width = '100%';
        progressText.textContent = 'Готово!';

        // Show results
        const savedPct = totalOriginal > 0
            ? Math.round((1 - totalCompressed / totalOriginal) * 100)
            : 0;

        resultStats.innerHTML = `
            <p>${formatSize(totalOriginal)} → ${formatSize(totalCompressed)} (экономия ${savedPct}%)</p>
        `;

        let filesHtml = '';
        const downloadLinks = [];

        results.forEach(r => {
            if (r.error) {
                filesHtml += `<div class="result-item"><span class="result-item-name">❌ ${r.name}</span><span class="result-item-info">${r.error}</span></div>`;
            } else {
                const saved = Math.round((1 - r.compressed_size / r.original_size) * 100);
                filesHtml += `<div class="result-item"><div><div class="result-item-name">${r.original_name}</div><div class="result-item-info">${formatSize(r.original_size)} → ${formatSize(r.compressed_size)} (−${saved}%)</div></div><a href="#" onclick="forceDownload('${r.download_url}', '${r.original_name}'); return false;" class="btn btn-secondary" style="padding:8px 16px;font-size:0.85rem;">Скачать</a></div>`;
                downloadLinks.push(r.download_url);
            }
        });

        document.getElementById('result-files').innerHTML = filesHtml;
        document.getElementById('result-area').style.display = 'block';
        document.getElementById('empty-result-state').style.display = 'none';

        resultArea.classList.add('active');

        // Download all (one by one for now)
        if (downloadLinks.length > 1) {
            downloadAllBtn.style.display = '';
            downloadAllBtn.onclick = () => downloadAsZip(downloadLinks);
        }

        setTimeout(() => {
            progress.classList.remove('active');
        }, 1500);

        btn.disabled = false;
        btn.innerHTML = '🗜️ Сжать изображения';
    });
});
