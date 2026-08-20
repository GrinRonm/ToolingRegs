document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('json-input');
    const output = document.getElementById('json-output');
    const errorDiv = document.getElementById('json-error');

    document.getElementById('btn-beautify').addEventListener('click', () => {
        try {
            const parsed = JSON.parse(input.value);
            output.value = JSON.stringify(parsed, null, 2);
            errorDiv.style.display = 'none';
            showToast('JSON отформатирован!', 'success');
        } catch(e) {
            errorDiv.textContent = '❌ Ошибка: ' + e.message;
            errorDiv.style.display = '';
            output.value = '';
        }
    });

    document.getElementById('btn-minify').addEventListener('click', () => {
        try {
            const parsed = JSON.parse(input.value);
            output.value = JSON.stringify(parsed);
            errorDiv.style.display = 'none';
            showToast('JSON минифицирован!', 'success');
        } catch(e) {
            errorDiv.textContent = '❌ Ошибка: ' + e.message;
            errorDiv.style.display = '';
            output.value = '';
        }
    });

    document.getElementById('btn-clear').addEventListener('click', () => {
        input.value = '';
        output.value = '';
        errorDiv.style.display = 'none';
    });

    document.getElementById('btn-copy').addEventListener('click', () => {
        navigator.clipboard.writeText(output.value).then(() => {
            const btn = document.getElementById('btn-copy');
            btn.textContent = '✅ Скопировано!';
            btn.classList.add('copied');
            setTimeout(() => { btn.textContent = '📋 Копировать'; btn.classList.remove('copied'); }, 2000);
        });
    });
});
