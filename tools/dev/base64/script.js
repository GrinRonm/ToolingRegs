document.addEventListener('DOMContentLoaded', () => {
    const textInput = document.getElementById('text-input');
    const b64Output = document.getElementById('base64-output');

    document.getElementById('btn-encode').addEventListener('click', () => {
        try {
            b64Output.value = btoa(unescape(encodeURIComponent(textInput.value)));
            showToast('Закодировано!', 'success');
        } catch(e) { showToast('Ошибка кодирования', 'error'); }
    });

    document.getElementById('btn-decode').addEventListener('click', () => {
        try {
            textInput.value = decodeURIComponent(escape(atob(b64Output.value.trim())));
            showToast('Декодировано!', 'success');
        } catch(e) { showToast('Невалидный Base64', 'error'); }
    });

    document.getElementById('btn-clear').addEventListener('click', () => {
        textInput.value = ''; b64Output.value = '';
    });

    document.getElementById('btn-copy').addEventListener('click', () => {
        navigator.clipboard.writeText(b64Output.value).then(() => {
            const btn = document.getElementById('btn-copy');
            btn.textContent = '✅ Скопировано!'; btn.classList.add('copied');
            setTimeout(() => { btn.textContent = '📋 Копировать'; btn.classList.remove('copied'); }, 2000);
        });
    });
});
