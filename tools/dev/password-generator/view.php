<div style="max-width:500px; margin:0 auto;">
    <div class="output-box" style="margin-bottom:24px;">
        <textarea id="password-output" rows="2" readonly style="font-size:1.5rem; text-align:center; font-family:monospace; padding-right:16px; min-height:80px; display:flex; align-items:center;" placeholder="Сгенерированный пароль"></textarea>
        <button class="copy-btn" id="btn-copy" style="top:20px; right:16px;">📋 Копировать</button>
    </div>

    <div class="options-panel" style="display:block;">
        <h3>Настройки пароля</h3>
        
        <div class="option-row">
            <span class="option-label">Длина (<span id="len-val">16</span>)</span>
            <input type="range" id="pass-length" min="6" max="64" value="16">
        </div>
        
        <div class="option-row" style="margin-top:16px;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; flex:1;">
                <input type="checkbox" id="pass-upper" checked> A-Z (Заглавные буквы)
            </label>
        </div>
        
        <div class="option-row">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; flex:1;">
                <input type="checkbox" id="pass-lower" checked> a-z (Строчные буквы)
            </label>
        </div>
        
        <div class="option-row">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; flex:1;">
                <input type="checkbox" id="pass-numbers" checked> 0-9 (Цифры)
            </label>
        </div>
        
        <div class="option-row">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; flex:1;">
                <input type="checkbox" id="pass-symbols" checked> !@#$ (Спецсимволы)
            </label>
        </div>
    </div>
    
    <div class="btn-group" style="justify-content:center; margin-top:24px;">
        <button class="btn btn-primary" id="btn-generate" style="width:100%;">🔑 Сгенерировать новый пароль</button>
    </div>
</div>
