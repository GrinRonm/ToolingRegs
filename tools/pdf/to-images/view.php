<div class="text-columns">
    <div class="text-column">
        <label>PDF Файл</label>
        <div class="upload-zone" id="upload-zone" style="min-height:220px; display:flex; flex-direction:column; justify-content:center;">
            <div class="upload-zone-content">
                <span class="upload-zone-icon">🖼️</span>
                <h3>Вставьте или перетащите</h3>
                <p>Каждая страница будет сохранена как изображение</p>
            </div>
            <input type="file" id="file-input" accept="application/pdf">
        </div>
        
        <div class="file-list" id="file-list" style="margin-top:12px;"></div>

        <div class="options-panel" id="options-panel" style="display:none; margin-top:16px;">
            <h3>Настройки</h3>
            <div class="option-row">
                <span class="option-label">Формат</span>
                <select id="image-format" style="width:120px;">
                    <option value="jpg">JPG</option>
                    <option value="png">PNG</option>
                </select>
            </div>
        </div>

        <div class="btn-group" id="action-buttons" style="display:none; margin-top:16px;">
            <button class="btn btn-primary" id="btn-convert" style="flex:1;">🖼️ Конвертировать</button>
            <button class="btn btn-secondary" id="btn-clear">Очистить</button>
        </div>

        <div class="progress-wrap" id="progress-wrap" style="margin-top:16px;">
            <div class="progress-bar-outer"><div class="progress-bar-inner" id="progress-bar"></div></div>
            <div class="progress-text" id="progress-text">Конвертация...</div>
        </div>
    </div>

    <div class="text-column">
        <label>Результат</label>
        <div id="result-area" class="result-area" style="display:none; margin-top:0;">
            <div class="result-success">
                <h3>✅ PDF конвертирован!</h3>
                <div id="result-stats"></div>
                <button id="btn-download-all" class="btn btn-success" style="display:none; margin-top:12px; width:100%;">📦 Скачать всё (ZIP)</button>
            </div>
            <div id="result-files"></div>
        </div>
        
        <div id="empty-result-state" style="background:var(--bg-card); border-radius:12px; border:1px solid var(--border-color); height:100%; min-height:220px; display:flex; align-items:center; justify-content:center; color:var(--text-muted);">
            <div style="text-align:center;">
                <span style="font-size:2rem; opacity:0.5; display:block; margin-bottom:8px;">🖼️</span>
                Изображения появятся здесь
            </div>
        </div>
    </div>
</div>
