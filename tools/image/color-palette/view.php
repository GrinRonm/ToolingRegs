<div class="text-columns">
    <div class="text-column">
        <label>Изображение</label>
        <div class="upload-zone" id="upload-zone" style="min-height:250px;">
            <div class="upload-zone-content">
                <span class="upload-zone-icon">🎨</span>
                <h3>Вставьте или перетащите картинку</h3>
                <p>Мы найдём 5 основных цветов</p>
            </div>
            <input type="file" id="file-input" accept="image/jpeg, image/png, image/webp">
        </div>
        <div class="file-list" id="file-list" style="margin-top:12px;"></div>
        
        <div class="btn-group" style="margin-top:16px; display:none;" id="action-buttons">
            <button class="btn btn-primary" id="btn-process" style="flex:1;">🎨 Получить палитру</button>
            <button class="btn btn-secondary" id="btn-clear">Очистить</button>
        </div>
    </div>

    <div class="text-column">
        <label>Результат (Палитра)</label>
        
        <div id="result-area" class="result-area" style="display:none; height:100%;">
            <div id="palette-colors" style="display:flex; flex-direction:column; gap:12px; height:100%;"></div>
        </div>

        <div id="empty-result-state" style="background:var(--bg-card); border-radius:12px; border:1px solid var(--border-color); height:100%; min-height:250px; display:flex; align-items:center; justify-content:center; color:var(--text-muted);">
            <div style="text-align:center;">
                <span style="font-size:2rem; opacity:0.5; display:block; margin-bottom:8px;">🎨</span>
                Здесь появятся цвета
            </div>
        </div>
    </div>
</div>
