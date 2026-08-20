<style>
.diff-container { display: flex; flex-direction: column; gap: 16px; margin-top: 20px; }
.diff-panels { display: flex; gap: 16px; min-height: 300px; }
.diff-panel { flex: 1; display: flex; flex-direction: column; }
.diff-panel textarea { flex: 1; resize: vertical; min-height: 250px; font-family: monospace; font-size: 14px; }
.diff-result { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 16px; font-family: monospace; font-size: 14px; white-space: pre-wrap; overflow-x: auto; min-height: 250px; }
.diff-added { background-color: rgba(16, 185, 129, 0.2); color: #047857; text-decoration: none; }
.diff-removed { background-color: rgba(239, 68, 68, 0.2); color: #b91c1c; text-decoration: line-through; }
.diff-grey { color: #6b7280; }
[data-theme="dark"] .diff-added { color: #34d399; }
[data-theme="dark"] .diff-removed { color: #f87171; }
[data-theme="dark"] .diff-grey { color: #9ca3af; }
</style>

<div class="diff-container">
    <div class="diff-panels">
        <div class="diff-panel">
            <label>Оригинальный текст</label>
            <textarea id="text-old" class="code-editor" placeholder="Вставьте старый текст..."></textarea>
        </div>
        <div class="diff-panel">
            <label>Изменённый текст</label>
            <textarea id="text-new" class="code-editor" placeholder="Вставьте новый текст..."></textarea>
        </div>
    </div>
    
    <div style="text-align: center;">
        <button id="btn-compare" class="btn btn-primary">Сравнить тексты</button>
    </div>

    <div>
        <label>Результат сравнения</label>
        <div id="diff-output" class="diff-result">Здесь появится разница...</div>
    </div>
</div>

<!-- Подключаем библиотеку jsdiff через CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsdiff/7.0.0/diff.min.js"></script>
