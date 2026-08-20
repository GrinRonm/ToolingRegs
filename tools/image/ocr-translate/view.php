<div class="text-columns">
    <!-- Left Column: Upload -->
    <div class="text-column">
        <label>Изображение</label>
        
        <div class="upload-zone" id="upload-zone" style="min-height:220px; display:flex; flex-direction:column; justify-content:center;">
            <div class="upload-zone-content">
                <span class="upload-zone-icon">📖</span>
                <h3>Вставьте или перетащите</h3>
                <p>Скриншот, фото с текстом — JPG, PNG, WEBP</p>
                <div class="paste-hint">
                    Нажмите <kbd>Ctrl</kbd> + <kbd>V</kbd>
                </div>
            </div>
            <input type="file" id="file-input" accept="image/*">
        </div>
        
        <div class="file-list" id="file-list" style="margin-top:12px;"></div>

        <div class="options-panel" style="margin-top:16px;">
            <div class="option-row">
                <span class="option-label">Язык текста</span>
                <select id="ocr-lang" style="width:100%;">
                    <option value="eng+rus">Английский + Русский</option>
                    <option value="rus+eng">Русский + Английский</option>
                    <option value="rus">Только Русский</option>
                    <option value="eng">Только Английский</option>
                </select>
            </div>
        </div>
        
        <div class="btn-group" id="action-buttons" style="display:none; margin-top:16px;">
            <button class="btn btn-primary" id="btn-extract" style="flex:1;">📖 Распознать заново</button>
            <button class="btn btn-secondary" id="btn-clear">Очистить</button>
        </div>
        
        <div class="progress-wrap" id="progress-wrap" style="margin-top:16px;">
            <div class="progress-bar-outer">
                <div class="progress-bar-inner" id="progress-bar"></div>
            </div>
            <div class="progress-text" id="progress-text">Распознавание текста...</div>
        </div>
    </div>

    <!-- Right Column: Result -->
    <div class="text-column">
        <label>Результат</label>
        
        <div id="empty-result-state" style="background:var(--bg-card); border-radius:12px; border:1px solid var(--border-color); height:100%; min-height:220px; display:flex; align-items:center; justify-content:center; color:var(--text-muted);">
            <div style="text-align:center;">
                <span style="font-size:2rem; opacity:0.5; display:block; margin-bottom:8px;">📝</span>
                Текст появится здесь
            </div>
        </div>
        
        <div id="result-area" class="result-area" style="display:none; margin-top:0;">
            <!-- Extracted Text -->
            <div class="output-box">
                <label style="font-size:0.85rem;color:var(--text-secondary);font-weight:500;display:block;margin-bottom:6px;">Извлечённый текст</label>
                <textarea id="extracted-text" rows="6" placeholder="Извлечённый текст"></textarea>
                <button class="copy-btn" id="btn-copy-text">📋 Копировать</button>
            </div>

            <!-- Translation Section -->
            <div class="options-panel" style="margin-top:20px; padding:12px;">
                <div class="option-row" style="flex-wrap:wrap;gap:8px;">
                    <select id="source-lang" style="flex:1;min-width:100px;">
                        <option value="en">Английский</option>
                        <option value="ru">Русский</option>
                        <option value="de">Немецкий</option>
                        <option value="fr">Французский</option>
                        <option value="es">Испанский</option>
                        <option value="auto">Авто</option>
                    </select>
                    <button class="swap-btn" id="btn-swap-langs" title="Поменять языки местами">⇄</button>
                    <select id="target-lang" style="flex:1;min-width:100px;">
                        <option value="ru">Русский</option>
                        <option value="en">Английский</option>
                        <option value="de">Немецкий</option>
                        <option value="fr">Французский</option>
                        <option value="es">Испанский</option>
                    </select>
                </div>
                <div class="btn-group" style="margin-top:12px;">
                    <button class="btn btn-primary" id="btn-translate" style="width:100%;">🌍 Перевести</button>
                </div>
            </div>

            <!-- Translated Text -->
            <div class="output-box" id="translation-result" style="display:none;margin-top:16px;">
                <label style="font-size:0.85rem;color:var(--text-secondary);font-weight:500;display:block;margin-bottom:6px;">Перевод</label>
                <textarea id="translated-text" rows="6" readonly></textarea>
                <button class="copy-btn" id="btn-copy-translation">📋 Копировать</button>
            </div>
        </div>
    </div>
</div>
