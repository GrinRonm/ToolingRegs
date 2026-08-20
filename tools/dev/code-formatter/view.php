<div class="text-columns">
    <div class="text-column">
        <label>Исходный код</label>
        <div style="margin-bottom: 12px; display: flex; gap: 8px;">
            <select id="code-lang" class="form-control" style="width: auto;">
                <option value="json">JSON</option>
                <option value="html">HTML</option>
                <option value="css">CSS</option>
                <option value="js">JavaScript</option>
                <option value="sql">SQL</option>
            </select>
            <button id="btn-format" class="btn btn-primary">✨ Форматировать</button>
            <button id="btn-minify" class="btn btn-secondary">🗜️ Минифицировать</button>
        </div>
        <textarea id="code-input" class="code-editor" style="height: 600px; resize: vertical;" placeholder="Вставьте ваш код сюда..."></textarea>
    </div>

    <div class="text-column">
        <label>Результат</label>
        <div style="margin-bottom: 12px; display: flex; justify-content: flex-end;">
            <button id="btn-copy" class="btn btn-secondary">📋 Копировать</button>
        </div>
        <textarea id="code-output" class="code-editor" style="height: 600px; resize: vertical;" readonly placeholder="Здесь появится отформатированный код..."></textarea>
    </div>
</div>

<!-- Подключаем Prettier -->
<script src="https://unpkg.com/prettier@2.8.8/standalone.js"></script>
<script src="https://unpkg.com/prettier@2.8.8/parser-html.js"></script>
<script src="https://unpkg.com/prettier@2.8.8/parser-postcss.js"></script>
<script src="https://unpkg.com/prettier@2.8.8/parser-babel.js"></script>
<!-- Подключаем sql-formatter -->
<script src="https://unpkg.com/sql-formatter@12.2.1/dist/sql-formatter.min.js"></script>
