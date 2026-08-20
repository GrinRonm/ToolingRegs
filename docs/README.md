# Instyment — Универсальная платформа онлайн-инструментов

## Описание
Платформа с набором различных инструментов для работы с изображениями, PDF, текстом и данными.
Проект имеет модульную архитектуру, которая позволяет добавлять новые инструменты без изменения ядра.

## Требования
- PHP 7.4+
- SQLite3
- Python 3.8+
- Composer (опционально)
- Tesseract OCR (для инструмента извлечения текста)

## Зависимости Python
Установите зависимости через pip:
```bash
pip3 install pytesseract deep-translator PyPDF2 img2pdf qrcode[pil] Pillow
```

## Установка Tesseract OCR (Ubuntu/Debian)
```bash
sudo apt update
sudo apt install tesseract-ocr tesseract-ocr-rus tesseract-ocr-eng
```

## Установка платформы
1. Клонируйте проект в директорию (например, `/var/www/instyment`).
2. Убедитесь, что директории `storage/uploads`, `storage/processed`, `storage/temp` и `database` имеют права на запись для веб-сервера (например, `www-data`).
   ```bash
   mkdir -p storage/uploads storage/processed storage/temp database
   chown -R www-data:www-data storage database
   ```
3. Настройте Nginx (файл конфигурации `instyment` можно скопировать из примеров).
4. Добавьте задачу в cron для автоматической очистки временных файлов:
   ```bash
   0 * * * * php /var/www/instyment/scripts/cleanup.php
   ```

## Как добавить новый инструмент
Смотрите файл `CREATING_TOOL.md`.
