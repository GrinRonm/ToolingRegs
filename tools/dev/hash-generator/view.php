<div style="max-width:600px;margin:0 auto;">
    <textarea id="hash-input" rows="6" placeholder="Введите текст для хэширования..."></textarea>

    <div class="btn-group" style="justify-content:center;margin-bottom:20px;">
        <button class="btn btn-primary" id="btn-hash">🔢 Сгенерировать хэши</button>
        <button class="btn btn-secondary" id="btn-clear">Очистить</button>
    </div>

    <div id="hash-results" style="display:none;">
        <div class="result-item" style="flex-direction:column;align-items:flex-start;gap:4px;margin-bottom:8px;">
            <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                <strong style="font-size:0.8rem;color:var(--text-muted);">MD5</strong>
                <button class="copy-btn" data-target="hash-md5">📋</button>
            </div>
            <code id="hash-md5" style="font-size:0.85rem;word-break:break-all;width:100%;"></code>
        </div>
        <div class="result-item" style="flex-direction:column;align-items:flex-start;gap:4px;margin-bottom:8px;">
            <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                <strong style="font-size:0.8rem;color:var(--text-muted);">SHA-1</strong>
                <button class="copy-btn" data-target="hash-sha1">📋</button>
            </div>
            <code id="hash-sha1" style="font-size:0.85rem;word-break:break-all;width:100%;"></code>
        </div>
        <div class="result-item" style="flex-direction:column;align-items:flex-start;gap:4px;margin-bottom:8px;">
            <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                <strong style="font-size:0.8rem;color:var(--text-muted);">SHA-256</strong>
                <button class="copy-btn" data-target="hash-sha256">📋</button>
            </div>
            <code id="hash-sha256" style="font-size:0.85rem;word-break:break-all;width:100%;"></code>
        </div>
        <div class="result-item" style="flex-direction:column;align-items:flex-start;gap:4px;">
            <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                <strong style="font-size:0.8rem;color:var(--text-muted);">SHA-512</strong>
                <button class="copy-btn" data-target="hash-sha512">📋</button>
            </div>
            <code id="hash-sha512" style="font-size:0.85rem;word-break:break-all;width:100%;"></code>
        </div>
    </div>
</div>
