document.addEventListener('DOMContentLoaded', () => {
    const output = document.getElementById('password-output');
    const lenSlider = document.getElementById('pass-length');
    const lenVal = document.getElementById('len-val');
    
    const cbUpper = document.getElementById('pass-upper');
    const cbLower = document.getElementById('pass-lower');
    const cbNumbers = document.getElementById('pass-numbers');
    const cbSymbols = document.getElementById('pass-symbols');
    
    lenSlider.addEventListener('input', () => {
        lenVal.textContent = lenSlider.value;
        generatePassword();
    });

    const checkboxes = [cbUpper, cbLower, cbNumbers, cbSymbols];
    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            // Prevent unchecking all
            if (!cbUpper.checked && !cbLower.checked && !cbNumbers.checked && !cbSymbols.checked) {
                cb.checked = true;
            }
            generatePassword();
        });
    });

    document.getElementById('btn-generate').addEventListener('click', generatePassword);
    
    document.getElementById('btn-copy').addEventListener('click', () => {
        if (!output.value) return;
        navigator.clipboard.writeText(output.value).then(() => {
            const btn = document.getElementById('btn-copy');
            btn.textContent = '✅ Скопировано!';
            btn.classList.add('copied');
            setTimeout(() => { btn.textContent = '📋 Копировать'; btn.classList.remove('copied'); }, 2000);
        });
    });

    function generatePassword() {
        const length = parseInt(lenSlider.value);
        let charset = "";
        
        if (cbUpper.checked) charset += "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        if (cbLower.checked) charset += "abcdefghijklmnopqrstuvwxyz";
        if (cbNumbers.checked) charset += "0123456789";
        if (cbSymbols.checked) charset += "!@#$%^&*()_+~`|}{[]:;?><,./-=";
        
        if (charset === "") return;
        
        let password = "";
        
        // Use crypto API for secure random numbers if available
        if (window.crypto && window.crypto.getRandomValues) {
            const values = new Uint32Array(length);
            window.crypto.getRandomValues(values);
            for (let i = 0; i < length; i++) {
                password += charset[values[i] % charset.length];
            }
        } else {
            // Fallback for older browsers
            for (let i = 0; i < length; i++) {
                password += charset[Math.floor(Math.random() * charset.length)];
            }
        }
        
        output.value = password;
    }
    
    // Generate initial password
    generatePassword();
});
