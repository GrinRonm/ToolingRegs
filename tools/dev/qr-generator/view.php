<div class="text-columns">
    <div class="text-column">
        <label>Текст или URL</label>
        <textarea id="qr-input" rows="5" placeholder="Введите текст или ссылку для создания QR-кода..."></textarea>
        
        <div class="options-panel" style="display:block; margin-top:16px;">
            <div class="option-row">
                <span class="option-label">Размер</span>
                <input type="range" id="qr-size" min="100" max="1000" step="50" value="300">
                <span class="option-value" id="qr-size-val">300px</span>
            </div>
            <div class="option-row" style="margin-top:12px;">
                <span class="option-label">Цвет</span>
                <input type="color" id="qr-color" value="#000000" style="width:100%; height:32px; border:none; background:none; cursor:pointer;">
            </div>
        </div>
    </div>
    
    <div class="text-column">
        <label>Результат</label>
        <div class="upload-zone" style="min-height:250px; display:flex; align-items:center; justify-content:center; padding:16px; flex-direction:column; background:var(--bg-card);">
            <div id="qr-result" style="display:none; text-align:center;">
                <img id="qr-image" src="" alt="QR-код" style="max-width:100%; border-radius:8px; box-shadow:var(--shadow-md);">
                <div class="btn-group" style="justify-content:center; margin-top:16px;">
                    <a id="btn-download" href="#" download="qr-code.png" class="btn btn-primary" style="padding:8px 16px; font-size:0.9rem;">⬇️ Скачать PNG</a>
                </div>
            </div>
            <div id="qr-placeholder" style="color:var(--text-muted); text-align:center;">
                <span style="font-size:3rem; display:block; margin-bottom:8px; opacity:0.3;">📱</span>
                Здесь появится QR-код
            </div>
        </div>
    </div>
</div>

<div class="btn-group" style="justify-content:center; margin-top:24px;">
    <button class="btn btn-primary" id="btn-generate">📱 Сгенерировать</button>
</div>
