# Как создать свой инструмент за 5 минут

Платформа имеет модульную архитектуру. Вам не нужно редактировать ядро (`index.php`, роутеры или базу данных), чтобы добавить новый инструмент.

## Шаг 1: Создание папки
Выберите категорию (например, `image`, `pdf`, `dev`, `text`, `misc`) и создайте новую папку для вашего инструмента.

Например, вы хотите создать инструмент для конвертации HEX в RGB:
Создайте папку: `tools/dev/hex-to-rgb/`

## Шаг 2: Создание файлов инструмента
В папке инструмента должны находиться следующие файлы:

### 1. `manifest.json` (Обязательный)
Описывает ваш инструмент.
```json
{
    "id": "hex-to-rgb",
    "name": "HEX в RGB",
    "description": "Конвертация цветов из формата HEX в RGB",
    "category": "dev",
    "icon": "🎨",
    "tags": ["hex", "rgb", "color", "цвет", "конвертация"],
    "accepts": [],
    "max_files": 0,
    "supports_paste": false,
    "order": 10
}
```

### 2. `view.php` (Обязательный)
HTML-разметка страницы инструмента. Классы CSS уже подключены из `main.css`, так что вы можете использовать стандартные элементы (кнопки, текстовые поля и т.д.).
```php
<div class="options-panel">
    <div class="option-row">
        <label>HEX цвет</label>
        <input type="text" id="hex-input" placeholder="#ffffff">
    </div>
</div>
<div class="btn-group">
    <button class="btn btn-primary" id="btn-convert">🎨 Конвертировать</button>
</div>
<div class="output-box" style="margin-top:20px;">
    <textarea id="rgb-output" rows="2" readonly></textarea>
</div>
```

### 3. `script.js` (Опционально)
Ваш клиентский JavaScript.
```javascript
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('btn-convert').addEventListener('click', () => {
        const hex = document.getElementById('hex-input').value;
        // логика конвертации ...
        document.getElementById('rgb-output').value = `rgb(...)`;
    });
});
```

### 4. `handler.php` (Опционально)
Если инструмент требует серверной обработки (например, конвертация файла, загрузка, Python-скрипт).
```php
<?php
// Возвращайте массив, он автоматически преобразуется в JSON-ответ.
return [
    'success' => true,
    'result' => 'ваш_результат'
];
```

## Шаг 3: Готово!
Сразу после сохранения `manifest.json` ваш инструмент **автоматически** появится на главной странице, в поиске и получит собственный URL вида `/tool/dev/hex-to-rgb`.
