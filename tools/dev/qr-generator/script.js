document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('qr-input');
    const sizeSlider = document.getElementById('qr-size');
    const sizeVal = document.getElementById('qr-size-val');
    const colorInput = document.getElementById('qr-color');
    const resultDiv = document.getElementById('qr-result');
    const placeholderDiv = document.getElementById('qr-placeholder');
    const img = document.getElementById('qr-image');
    const btnDownload = document.getElementById('btn-download');
    
    sizeSlider.addEventListener('input', () => {
        sizeVal.textContent = sizeSlider.value + 'px';
    });

    document.getElementById('btn-generate').addEventListener('click', async () => {
        const text = input.value.trim();
        if (!text) {
            showToast('Введите текст или URL', 'error');
            return;
        }

        const size = sizeSlider.value;
        const color = colorInput.value;
        const btn = document.getElementById('btn-generate');
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Генерация...';

        try {
            const formData = new FormData();
            formData.append('text', text);
            formData.append('size', size);
            formData.append('color', color);
            formData.append('_token', getCsrfToken());

            const response = await fetch('/api/tool/qr-generator/process', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                img.src = data.url;
                btnDownload.onclick = (e) => {
                    e.preventDefault();
                    forceDownload(data.download_url, 'qr-code.png');
                };
                placeholderDiv.style.display = 'none';
                resultDiv.style.display = 'block';
                showToast('QR-код создан!', 'success');
            } else {
                showToast(data.error || 'Ошибка при создании', 'error');
            }
        } catch (error) {
            showToast('Ошибка сети', 'error');
        }

        btn.disabled = false;
        btn.innerHTML = '📱 Сгенерировать';
    });
});
