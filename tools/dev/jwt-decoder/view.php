<div class="text-columns">
    <!-- Левая колонка: Ввод токена -->
    <div class="text-column">
        <label>JWT Токен</label>
        <textarea id="jwt-input" class="code-editor" style="height: 100%; min-height: 250px; resize: none;" placeholder="Вставьте ваш JWT токен сюда...
(Например: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...)"></textarea>
    </div>

    <!-- Правая колонка: Результат -->
    <div class="text-column">
        <label>Расшифрованные данные</label>
        
        <div style="margin-bottom: 12px;">
            <strong>Header (Заголовок):</strong>
            <pre id="jwt-header" style="background:var(--bg-card); padding:12px; border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; font-size:0.9rem; margin-top:4px;">{}</pre>
        </div>
        
        <div style="margin-bottom: 12px;">
            <strong>Payload (Полезная нагрузка):</strong>
            <pre id="jwt-payload" style="background:var(--bg-card); padding:12px; border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; font-size:0.9rem; margin-top:4px;">{}</pre>
        </div>

        <div style="margin-bottom: 12px;">
            <strong>Signature (Подпись):</strong>
            <pre id="jwt-signature" style="background:var(--bg-card); padding:12px; border-radius:8px; border:1px solid var(--border-color); overflow-x:auto; font-size:0.9rem; margin-top:4px; word-break:break-all;"></pre>
        </div>
    </div>
</div>
