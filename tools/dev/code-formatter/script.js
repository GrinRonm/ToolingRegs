document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('code-input');
    const output = document.getElementById('code-output');
    const langSelect = document.getElementById('code-lang');
    
    document.getElementById('btn-copy').addEventListener('click', () => {
        if (!output.value) return;
        navigator.clipboard.writeText(output.value).then(() => {
            showToast('Код скопирован!', 'success');
        });
    });

    document.getElementById('btn-format').addEventListener('click', () => processCode(false));
    document.getElementById('btn-minify').addEventListener('click', () => processCode(true));

    function processCode(isMinify) {
        const code = input.value.trim();
        const lang = langSelect.value;
        if (!code) {
            showToast('Вставьте код', 'error');
            return;
        }

        try {
            let res = '';
            
            if (lang === 'sql') {
                if (isMinify) {
                    res = code.replace(/\s+/g, ' ').trim();
                } else {
                    res = sqlFormatter.format(code, { language: 'sql', tabWidth: 4 });
                }
            } else {
                // Prettier for HTML/CSS/JS/JSON
                const parsers = {
                    html: 'html',
                    css: 'css',
                    js: 'babel',
                    json: 'json'
                };
                
                const plugins = {
                    html: [prettierPlugins.html],
                    css: [prettierPlugins.postcss],
                    js: [prettierPlugins.babel],
                    json: [prettierPlugins.babel] // babel plugin includes json parser
                };

                if (isMinify) {
                    // Minification
                    if (lang === 'json') {
                        try {
                            // Try strict JSON parse first
                            res = JSON.stringify(JSON.parse(code));
                        } catch(e) {
                            // Fallback to strip spaces
                            res = code.replace(/\n/g, '').replace(/\s{2,}/g, ' ');
                        }
                    } else if (lang === 'css' || lang === 'js' || lang === 'html') {
                        res = prettier.format(code, {
                            parser: parsers[lang],
                            plugins: plugins[lang],
                            printWidth: 10000,
                            tabWidth: 0,
                            singleQuote: true,
                            bracketSpacing: false
                        }).replace(/\n/g, '').replace(/\s{2,}/g, ' ');
                    }
                } else {
                    res = prettier.format(code, {
                        parser: parsers[lang],
                        plugins: plugins[lang],
                        tabWidth: 4,
                        printWidth: 80
                    });
                }
            }

            output.value = res;
        } catch (e) {
            showToast('Синтаксическая ошибка: ' + e.message, 'error');
        }
    }
});
