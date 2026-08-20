<div class="text-columns">
    <div class="text-column">
        <label>Входной JSON</label>
        <textarea id="json-input" rows="15" placeholder='Вставьте JSON сюда...'></textarea>
    </div>
    <div class="text-column">
        <label>Результат</label>
        <div class="output-box">
            <textarea id="json-output" rows="15" readonly placeholder="Отформатированный JSON"></textarea>
            <button class="copy-btn" id="btn-copy">📋 Копировать</button>
        </div>
    </div>
</div>
<div class="btn-group" style="justify-content:center;">
    <button class="btn btn-primary" id="btn-beautify">✨ Форматировать</button>
    <button class="btn btn-secondary" id="btn-minify">📦 Минифицировать</button>
    <button class="btn btn-secondary" id="btn-clear">Очистить</button>
</div>
<div id="json-error" style="margin-top:12px;display:none;" class="result-error"></div>
