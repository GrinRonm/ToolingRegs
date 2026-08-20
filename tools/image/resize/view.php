<div class="text-columns">
    <div class="text-column">
        <label>Изображения</label>
        <div class="upload-zone" id="upload-zone" style="min-height:220px; display:flex; flex-direction:column; justify-content:center;">
            <div class="upload-zone-content">
                <span class="upload-zone-icon">📐</span>
                <h3>Вставьте или перетащите</h3>
                <p>JPG, PNG, WEBP — до 10 файлов</p>
                <div class="paste-hint">Нажмите <kbd>Ctrl</kbd> + <kbd>V</kbd></div>
            </div>
            <input type="file" id="file-input" multiple accept="image/jpeg,image/png,image/webp">
        </div>
        
        <div class="file-list" id="file-list" style="margin-top:12px;"></div>

        <div class="options-panel" id="options-panel" style="display:none; margin-top:16px;">
            <h3>Новый размер</h3>
            <div class="option-row">
                <span class="option-label">Ширина (px)</span>
                <input type="number" id="resize-width" value="800" min="1" max="10000" style="width:120px;">
            </div>
            <div class="option-row">
                <span class="option-label">Высота (px)</span>
                <input type="number" id="resize-height" value="" placeholder="Авто" min="1" max="10000" style="width:120px;">
            </div>
            <div class="option-row">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="checkbox" id="keep-aspect" checked> Сохранять пропорции
                </label>
            </div>
        </div>

        <div class="btn-group" id="action-buttons" style="display:none; margin-top:16px;">
            <button class="btn btn-primary" id="btn-resize" style="flex:1;">📐 Изменить размер</button>
            <button class="btn btn-secondary" id="btn-clear">Очистить</button>
        </div>

        <div class="progress-wrap" id="progress-wrap" style="margin-top:16px;">
            <div class="progress-bar-outer"><div class="progress-bar-inner" id="progress-bar"></div></div>
            <div class="progress-text" id="progress-text">Обработка...</div>
        </div>
    </div>

    <div class="text-column">
        <label>Результат</label>
        <div id="result-area" class="result-area" style="display:none; margin-top:0;">
            <div class="result-success">
                <h3>✅ Готово!</h3>
            </div>
            <div id="result-files"></div>
        </div>
        
        <div id="empty-result-state" style="background:var(--bg-card); border-radius:12px; border:1px solid var(--border-color); height:100%; min-height:220px; display:flex; align-items:center; justify-content:center; color:var(--text-muted);">
            <div style="text-align:center;">
                <span style="font-size:2rem; opacity:0.5; display:block; margin-bottom:8px;">📐</span>
                Изображения появятся здесь
            </div>
        </div>
    </div>
</div>
