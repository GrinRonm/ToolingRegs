<div class="text-columns">
    <div class="text-column">
        <label>Текст</label>
        <textarea id="text-input" rows="10" placeholder="Введите текст..."></textarea>
    </div>
    <div class="text-column">
        <label>Base64</label>
        <div class="output-box">
            <textarea id="base64-output" rows="10" placeholder="Результат Base64"></textarea>
            <button class="copy-btn" id="btn-copy">📋 Копировать</button>
        </div>
    </div>
</div>
<div class="btn-group" style="justify-content:center;">
    <button class="btn btn-primary" id="btn-encode">🔒 Кодировать → Base64</button>
    <button class="btn btn-secondary" id="btn-decode">🔓 Декодировать ← Base64</button>
    <button class="btn btn-secondary" id="btn-clear">Очистить</button>
</div>
