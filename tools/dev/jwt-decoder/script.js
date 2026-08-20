document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('jwt-input');
    const headerPre = document.getElementById('jwt-header');
    const payloadPre = document.getElementById('jwt-payload');
    const signaturePre = document.getElementById('jwt-signature');

    function decodeJWT(token) {
        try {
            const parts = token.split('.');
            if (parts.length !== 3) {
                return null;
            }

            const header = JSON.parse(atob(parts[0].replace(/-/g, '+').replace(/_/g, '/')));
            const payload = JSON.parse(atob(parts[1].replace(/-/g, '+').replace(/_/g, '/')));
            const signature = parts[2];

            return { header, payload, signature };
        } catch (e) {
            return null;
        }
    }

    input.addEventListener('input', () => {
        const token = input.value.trim();
        if (!token) {
            headerPre.textContent = '{}';
            payloadPre.textContent = '{}';
            signaturePre.textContent = '';
            input.style.borderColor = 'var(--border-color)';
            return;
        }

        const decoded = decodeJWT(token);
        if (decoded) {
            headerPre.textContent = JSON.stringify(decoded.header, null, 2);
            payloadPre.textContent = JSON.stringify(decoded.payload, null, 2);
            signaturePre.textContent = decoded.signature;
            input.style.borderColor = '#10b981'; // Green
        } else {
            headerPre.textContent = 'Ошибка парсинга (Неверный токен)';
            payloadPre.textContent = 'Ошибка парсинга (Неверный токен)';
            signaturePre.textContent = '';
            input.style.borderColor = '#ef4444'; // Red
        }
    });
});
