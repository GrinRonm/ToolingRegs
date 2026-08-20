# Instyment — Универсальная платформа онлайн-инструментов

Создание модульной платформы с онлайн-инструментами для работы с изображениями, документами, PDF, текстом и другими задачами.

## Серверное окружение

| Компонент | Версия/Статус |
|-----------|---------------|
| PHP | 7.4.3 (с GD, SQLite, cURL, mbstring, fileinfo) |
| Python | 3.8.10 (Pillow установлен) |
| SQLite | 3.31.1 |
| Nginx | Установлен |
| Composer | Установлен |
| Tesseract OCR | **Нужно установить** |

## User Review Required

> [!IMPORTANT]
> **Домен / Nginx:** Нужно ли настроить отдельный nginx конфиг для этого проекта (например `instyment.ae0.ru`)? Или пока тестируем на localhost / по IP?

> [!IMPORTANT]
> **Tesseract OCR:** Для распознавания текста с изображений нужно установить `tesseract-ocr` и языковые пакеты (`tesseract-ocr-rus`, `tesseract-ocr-eng`). Разрешаете установить через `apt install`?

> [!WARNING]
> **Python пакеты:** Нужно доустановить: `pytesseract`, `deep-translator`, `PyPDF2`, `img2pdf`. Разрешаете `pip3 install`?

## Архитектура проекта

```
/var/www/instyment/
├── index.php                    # Точка входа — роутер
├── .htaccess                    # Правила маршрутизации
├── config/
│   ├── app.php                  # Основные настройки
│   └── database.php             # SQLite настройки
├── core/
│   ├── App.php                  # Ядро приложения
│   ├── Router.php               # Маршрутизация
│   ├── ToolRegistry.php         # Автосканирование и регистрация модулей
│   ├── Database.php             # SQLite подключение (PDO singleton)
│   ├── Logger.php               # Логирование действий
│   ├── FileManager.php          # Загрузка, безопасность, очистка файлов
│   ├── Response.php             # JSON/HTML ответы
│   └── Security.php             # CSRF, Rate Limiting, валидация
├── templates/
│   ├── layout.php               # Основной шаблон (header/footer)
│   ├── home.php                 # Главная страница с карточками
│   └── tool.php                 # Обёртка для страницы инструмента
├── assets/
│   ├── css/
│   │   └── main.css             # Главный CSS (дизайн, тема)
│   ├── js/
│   │   ├── app.js               # Основной JS (поиск, навигация)
│   │   └── uploader.js          # Общий загрузчик (D&D, Ctrl+V, файлы)
│   └── img/                     # Статика (логотип, иконки)
├── tools/                       # ← Все модули инструментов
│   ├── image/
│   │   ├── ocr-translate/       # OCR + перевод
│   │   ├── compress/            # Сжатие изображений (вкл. массовое)
│   │   ├── convert/             # Конвертация форматов (jpg↔png↔webp)
│   │   └── resize/              # Изменение размера
│   ├── pdf/
│   │   ├── merge/               # Объединение PDF
│   │   ├── split/               # Разделение PDF
│   │   ├── compress/            # Сжатие PDF
│   │   └── to-images/           # PDF → Изображения
│   └── dev/
│       ├── json-formatter/      # Форматирование JSON
│       ├── base64/              # Base64 encode/decode
│       ├── hash-generator/      # MD5, SHA1, SHA256
│       ├── qr-generator/       # QR-коды
│       └── password-generator/  # Генератор паролей
├── python/
│   ├── worker.py                # Python worker для тяжёлых задач
│   ├── ocr_handler.py           # OCR через Tesseract
│   ├── translate_handler.py     # Перевод текста
│   └── pdf_handler.py           # Работа с PDF
├── storage/
│   ├── uploads/                 # Загруженные файлы (временно)
│   ├── processed/               # Обработанные файлы (временно)
│   └── temp/                    # Временные файлы
├── database/
│   └── instyment.db             # SQLite база
├── scripts/
│   └── cleanup.php              # Очистка старых файлов (cron)
└── docs/
    ├── README.md
    └── CREATING_TOOL.md         # Как создать свой инструмент
```

## Структура модуля (каждый инструмент)

Каждый инструмент — это папка с файлами:

```
tools/image/compress/
├── manifest.json        # Метаданные: name, description, category, icon, tags
├── handler.php          # Backend обработка (API endpoint)
├── view.php             # Frontend HTML страницы инструмента
├── script.js            # JS логика конкретного инструмента
├── style.css            # CSS специфичный для инструмента (опционально)
└── python_handler.py    # Python обработчик (опционально)
```

**manifest.json** пример:
```json
{
    "id": "image-compress",
    "name": "Сжатие изображений",
    "description": "Уменьшите размер JPG, PNG и WEBP без потери качества",
    "category": "image",
    "icon": "🗜️",
    "tags": ["compress", "jpg", "png", "webp", "сжатие", "размер"],
    "accepts": ["image/jpeg", "image/png", "image/webp"],
    "max_files": 20,
    "max_file_size": "50MB",
    "supports_paste": true,
    "supports_bulk": true
}
```

При запуске `ToolRegistry` автоматически сканирует `tools/` и регистрирует все найденные инструменты — **ничего не нужно прописывать вручную**.

---

## Proposed Changes

### Phase 1: Ядро платформы

#### [NEW] [index.php](file:///var/www/instyment/index.php)
Единая точка входа. Маршрутизация:
- `/` → Главная страница
- `/tool/{category}/{tool-id}` → Страница инструмента
- `/api/tools` → Список инструментов (JSON)
- `/api/tools/search?q=` → Поиск
- `/api/tool/{id}/process` → Обработка
- `/api/download/{file}` → Скачивание результата

#### [NEW] [config/app.php](file:///var/www/instyment/config/app.php)
Основные настройки: пути, лимиты, время жизни файлов.

#### [NEW] [core/Database.php](file:///var/www/instyment/core/Database.php)
PDO SQLite singleton. Создаёт таблицы при первом запуске:
- `logs` — действия пользователей
- `jobs` — очередь задач Python worker
- `stats` — статистика использования

#### [NEW] [core/ToolRegistry.php](file:///var/www/instyment/core/ToolRegistry.php)
Рекурсивно сканирует `tools/*/`, читает `manifest.json`, индексирует инструменты. Поддержка поиска по name/description/tags.

#### [NEW] [core/Logger.php](file:///var/www/instyment/core/Logger.php)
Логирование в SQLite: дата, IP, user-agent, session_id, tool, action, file info, result, duration, error.

#### [NEW] [core/FileManager.php](file:///var/www/instyment/core/FileManager.php)
Загрузка файлов: проверка MIME, расширения, размера. Уникальные имена. Хранение вне public.

#### [NEW] [core/Security.php](file:///var/www/instyment/core/Security.php)
CSRF-токены, Rate Limiting (через SQLite), валидация входных данных.

#### [NEW] [core/Response.php](file:///var/www/instyment/core/Response.php)
Единый формат JSON-ответов API.

---

### Phase 2: Frontend — Дизайн и шаблоны

#### [NEW] [assets/css/main.css](file:///var/www/instyment/assets/css/main.css)
Тёмная тема, современный SaaS-дизайн:
- CSS Custom Properties для цветовой палитры
- Glassmorphism эффекты на карточках
- Плавные анимации (hover, transitions)
- Адаптивная сетка карточек (CSS Grid)
- Стилизация drag & drop зоны
- Прогресс-бары
- Полная адаптивность (desktop-first → mobile)

#### [NEW] [assets/js/app.js](file:///var/www/instyment/assets/js/app.js)
- Live-поиск инструментов (debounce, без перезагрузки)
- Фильтрация по категориям
- Анимации при фильтрации карточек

#### [NEW] [assets/js/uploader.js](file:///var/www/instyment/assets/js/uploader.js)
Универсальный загрузчик, переиспользуемый всеми инструментами:
- Drag & Drop
- **Ctrl+V** (Clipboard API)
- Обычный `<input type="file">`
- Множественная загрузка
- Превью изображений
- Прогресс загрузки
- Валидация на клиенте

#### [NEW] [templates/layout.php](file:///var/www/instyment/templates/layout.php)
Основной HTML шаблон: header с лого и поиском, content, footer.

#### [NEW] [templates/home.php](file:///var/www/instyment/templates/home.php)
Главная страница: категории-табы + сетка карточек инструментов.

#### [NEW] [templates/tool.php](file:///var/www/instyment/templates/tool.php)
Обёртка страницы инструмента: хлебные крошки, заголовок, область загрузки, подключение JS/CSS инструмента.

---

### Phase 3: Инструменты (первая партия)

#### 🖼 Изображения

| Инструмент | ID | Описание |
|---|---|---|
| **OCR + Перевод** | `ocr-translate` | Извлечение текста из картинки (Tesseract), перевод EN↔RU (deep-translator). Поддержка Ctrl+V |
| **Сжатие** | `image-compress` | Сжатие JPG/PNG/WEBP с настройкой качества. Массовая загрузка. Скачивание ZIP |
| **Конвертация** | `image-convert` | JPG↔PNG↔WEBP. Выбор целевого формата. Массовая конвертация |
| **Изменение размера** | `image-resize` | Ресайз по ширине/высоте с сохранением пропорций |

#### 📄 PDF

| Инструмент | ID | Описание |
|---|---|---|
| **Объединение PDF** | `pdf-merge` | Объединение нескольких PDF в один. Drag для изменения порядка |
| **Разделение PDF** | `pdf-split` | Разделение PDF на отдельные страницы |
| **Сжатие PDF** | `pdf-compress` | Уменьшение размера PDF |
| **PDF → Изображения** | `pdf-to-images` | Конвертация страниц PDF в JPG/PNG |

#### 💻 Для разработчиков

| Инструмент | ID | Описание |
|---|---|---|
| **JSON Formatter** | `json-formatter` | Форматирование/минификация JSON. Подсветка синтаксиса |
| **Base64** | `base64` | Кодирование/декодирование Base64. Текст и файлы |
| **Hash Generator** | `hash-generator` | MD5, SHA1, SHA256, SHA512 |
| **QR Generator** | `qr-generator` | Генерация QR-кодов из текста/URL |
| **Генератор паролей** | `password-generator` | Надёжные пароли с настройками |

---

### Phase 4: Python Workers

#### [NEW] [python/worker.py](file:///var/www/instyment/python/worker.py)
Демон, который проверяет таблицу `jobs` в SQLite, берёт задачи (`status=queued`), выполняет, обновляет прогресс/результат.

#### [NEW] [python/ocr_handler.py](file:///var/www/instyment/python/ocr_handler.py)
OCR через pytesseract: поддержка русского и английского языков.

#### [NEW] [python/translate_handler.py](file:///var/www/instyment/python/translate_handler.py)
Перевод текста через deep-translator (Google Translate API, бесплатный).

#### [NEW] [python/pdf_handler.py](file:///var/www/instyment/python/pdf_handler.py)
Merge, split, compress PDF через PyPDF2/pikepdf.

---

### Phase 5: Инфраструктура

#### [NEW] [scripts/cleanup.php](file:///var/www/instyment/scripts/cleanup.php)
Cron-задача: удаление файлов старше N часов из `storage/`.

#### [NEW] [nginx конфиг]
```nginx
server {
    listen 80;
    server_name instyment.ae0.ru;  # или другой домен
    root /var/www/instyment;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    }

    location /storage/ {
        internal;
    }
}
```

#### [NEW] [docs/README.md](file:///var/www/instyment/docs/README.md)
Документация: установка, настройка, API.

#### [NEW] [docs/CREATING_TOOL.md](file:///var/www/instyment/docs/CREATING_TOOL.md)
Инструкция «Как добавить новый инструмент за 5 минут».

---

## База данных SQLite

```sql
-- Логи действий пользователей
CREATE TABLE logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip TEXT,
    user_agent TEXT,
    session_id TEXT,
    tool_id TEXT,
    action TEXT,
    file_name TEXT,
    file_type TEXT,
    file_size INTEGER,
    result TEXT,
    duration_ms INTEGER,
    error TEXT,
    job_id TEXT
);

-- Очередь задач для Python worker
CREATE TABLE jobs (
    id TEXT PRIMARY KEY,
    tool_id TEXT NOT NULL,
    status TEXT DEFAULT 'queued',  -- queued/processing/completed/failed
    input_data TEXT,               -- JSON с параметрами
    progress INTEGER DEFAULT 0,
    result TEXT,
    error TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME
);

-- Статистика использования
CREATE TABLE stats (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tool_id TEXT NOT NULL,
    date DATE DEFAULT (date('now')),
    count INTEGER DEFAULT 1,
    UNIQUE(tool_id, date)
);
```

## Verification Plan

### Automated Tests
- `php -l` — проверка синтаксиса всех PHP файлов
- `python3 -c "import pytesseract, deep_translator, PyPDF2"` — проверка Python зависимостей
- Curl-тесты API endpoints

### Manual Verification
- Открыть главную → увидеть все инструменты в карточках
- Поиск → найти инструмент по названию/тегу
- OCR → вставить картинку Ctrl+V → получить текст → перевести
- Сжатие → загрузить 5 фото → скачать ZIP
- Конвертация → JPG → PNG
- Проверить логи в БД
- Проверить мобильную адаптивность

## Порядок реализации

1. **Ядро** — config, core/*.php, database, index.php
2. **Frontend** — CSS, JS, templates
3. **Первые инструменты** — начинаем с image-compress и image-convert (полностью PHP+GD)
4. **Python worker + OCR/Translate** — установка Tesseract, pytesseract, deep-translator
5. **PDF инструменты** — PyPDF2
6. **Dev инструменты** — JSON formatter, Base64, Hash, QR, Password (чистый JS/PHP, без Python)
7. **Документация**
