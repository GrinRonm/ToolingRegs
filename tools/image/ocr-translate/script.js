document.addEventListener('DOMContentLoaded', () => {
    let currentFiles = [];

    const uploader = new Uploader({
        zone: '#upload-zone',
        fileInput: '#file-input',
        fileList: '#file-list',
        accepts: ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp'],
        maxFiles: 1,
        supportsPaste: true,
        onFilesChanged: (files) => {
            currentFiles = files;
            const hasFiles = files.length > 0;
            document.getElementById('action-buttons').style.display = hasFiles ? 'flex' : 'none';
            
            if (hasFiles) {
                // Automatically extract text when file is added
                extractText();
            } else {
                document.getElementById('result-area').style.display = 'none';
                document.getElementById('empty-result-state').style.display = 'flex';
                document.getElementById('progress-wrap').classList.remove('active');
                document.getElementById('translation-result').style.display = 'none';
                document.getElementById('extracted-text').value = '';
            }
        }
    });

    // Clear button
    document.getElementById('btn-clear').addEventListener('click', () => {
        uploader.clear();
    });

    // Re-extract button (if language changed)
    document.getElementById('btn-extract').addEventListener('click', () => {
        if (currentFiles.length > 0) {
            extractText();
        }
    });

    // Change language -> auto re-extract
    document.getElementById('ocr-lang').addEventListener('change', () => {
        if (currentFiles.length > 0) {
            extractText();
        }
    });

    async function extractText() {
        if (currentFiles.length === 0) return;

        const lang = document.getElementById('ocr-lang').value;
        const btn = document.getElementById('btn-extract');
        const progress = document.getElementById('progress-wrap');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const resultArea = document.getElementById('result-area');
        const emptyState = document.getElementById('empty-result-state');

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Распознавание...';
        progress.classList.add('active');
        
        // Hide result, show empty state while loading if no previous text
        // Or keep showing previous text but dim it. Let's just show progress.

        progressBar.style.width = '30%';
        progressText.textContent = 'Обработка изображения...';

        const formData = new FormData();
        formData.append('file', currentFiles[0]);
        formData.append('lang', lang);
        formData.append('_token', getCsrfToken());

        try {
            progressBar.style.width = '60%';
            progressText.textContent = 'Распознавание текста (OCR)...';

            const resp = await fetch('/api/tool/ocr-translate/process', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                body: formData
            });
            const data = await resp.json();

            progressBar.style.width = '100%';

            if (data.success) {
                document.getElementById('extracted-text').value = data.text;
                emptyState.style.display = 'none';
                resultArea.style.display = 'block';
                // Reset translation
                document.getElementById('translation-result').style.display = 'none';
                document.getElementById('translated-text').value = '';
                showToast('Текст извлечён!', 'success');
            } else {
                showToast(data.error || 'Ошибка распознавания', 'error');
            }
        } catch (err) {
            showToast('Ошибка сети', 'error');
        }

        setTimeout(() => progress.classList.remove('active'), 1000);
        btn.disabled = false;
        btn.innerHTML = '📖 Распознать заново';
    }

    // Translate button
    document.getElementById('btn-translate').addEventListener('click', async () => {
        const text = document.getElementById('extracted-text').value.trim();
        if (!text) {
            showToast('Нет текста для перевода', 'error');
            return;
        }

        const sourceLang = document.getElementById('source-lang').value;
        const targetLang = document.getElementById('target-lang').value;
        const btn = document.getElementById('btn-translate');

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Перевод...';

        try {
            const formData = new FormData();
            formData.append('text', text);
            formData.append('source', sourceLang);
            formData.append('target', targetLang);
            formData.append('action', 'translate');
            formData.append('_token', getCsrfToken());

            const resp = await fetch('/api/tool/ocr-translate/process', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                body: formData
            });
            const data = await resp.json();

            if (data.success) {
                document.getElementById('translated-text').value = data.translated_text;
                document.getElementById('translation-result').style.display = 'block';
                showToast('Перевод готов!', 'success');
            } else {
                showToast(data.error || 'Ошибка перевода', 'error');
            }
        } catch (err) {
            showToast('Ошибка сети', 'error');
        }

        btn.disabled = false;
        btn.innerHTML = '🌍 Перевести';
    });

    // Swap languages
    document.getElementById('btn-swap-langs').addEventListener('click', () => {
        const source = document.getElementById('source-lang');
        const target = document.getElementById('target-lang');
        const tmp = source.value;

        if (source.querySelector(`option[value="${target.value}"]`) &&
            target.querySelector(`option[value="${tmp}"]`)) {
            source.value = target.value;
            target.value = tmp;
        }
    });

    // Copy buttons
    document.getElementById('btn-copy-text').addEventListener('click', () => {
        copyToClipboard(document.getElementById('extracted-text').value, document.getElementById('btn-copy-text'));
    });

    document.getElementById('btn-copy-translation').addEventListener('click', () => {
        copyToClipboard(document.getElementById('translated-text').value, document.getElementById('btn-copy-translation'));
    });

    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const orig = btn.textContent;
            btn.textContent = '✅ Скопировано!';
            btn.classList.add('copied');
            setTimeout(() => {
                btn.textContent = orig;
                btn.classList.remove('copied');
            }, 2000);
        }).catch(() => {
            showToast('Не удалось скопировать', 'error');
        });
    }
});
