<div class="text-columns">
    <div class="text-column">
        <label>Изображения</label>
        <div class="upload-zone" id="upload-zone" style="min-height:250px;">
            <div class="upload-zone-content">
                <span class="upload-zone-icon">✨</span>
                <h3>Вставьте или перетащите картинку</h3>
                <p>Фон будет удалён автоматически (до 5 файлов)</p>
            </div>
            <input type="file" id="file-input" multiple accept="image/jpeg, image/png, image/webp">
        </div>
        <div class="file-list" id="file-list" style="margin-top:12px;"></div>
        
        <div class="btn-group" style="margin-top:16px; display:none;" id="action-buttons">
            <button class="btn btn-primary" id="btn-process" style="flex:1;">✨ Удалить фон</button>
            <button class="btn btn-secondary" id="btn-clear">Очистить</button>
        </div>
        
        <div class="progress-wrap" id="progress-wrap" style="margin-top:16px;">
            <div class="progress-bar-outer"><div class="progress-bar-inner" id="progress-bar"></div></div>
            <div class="progress-text" id="progress-text">Удаление фона (может занять время)...</div>
        </div>
    </div>

    <div class="text-column">
        <label>Результат</label>
        <div id="result-area" class="result-area" style="display:none; height:100%;">
            <div class="result-success">
                <h3>✅ Готово!</h3>
                <button id="btn-download-all" class="btn btn-success" style="display:none; margin-top:12px; width:100%;">📦 Скачать всё (ZIP)</button>
            </div>
            <div id="result-files"></div>
        </div>

        <div id="empty-result-state" style="background:var(--bg-card); border-radius:12px; border:1px solid var(--border-color); height:100%; min-height:250px; display:flex; align-items:center; justify-content:center; color:var(--text-muted);">
            <div style="text-align:center;">
                <span style="font-size:2rem; opacity:0.5; display:block; margin-bottom:8px;">🖼️</span>
                Здесь появятся прозрачные PNG
            </div>
        </div>
    </div>
</div>
