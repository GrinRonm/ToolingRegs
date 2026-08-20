#!/bin/bash

echo "=================================================="
echo "🚀 Установка и запуск Instyment..."
echo "=================================================="

# Убедимся, что скрипт выполняется с правами root
if [ "$EUID" -ne 0 ]; then
  echo "Пожалуйста, запустите скрипт от имени root (sudo ./install.sh)"
  exit 1
fi

echo "[1/4] Обновление пакетов и установка системных зависимостей..."
apt-get update -y
apt-get install -y curl wget git unzip zip software-properties-common

echo "[2/4] Установка PHP и Python..."
apt-get install -y \
    php-cli php-sqlite3 php-gd php-mbstring php-curl php-xml php-zip \
    python3 python3-pip python3-venv \
    libpng-dev libjpeg-dev libfreetype6-dev

echo "[3/4] Установка Python зависимостей..."
export PIP_BREAK_SYSTEM_PACKAGES=1
pip3 install --no-cache-dir rembg==2.0.40 pdf2docx pdfplumber==0.7.3 pandas openpyxl PyPDF2 pillow

echo "[4/4] Настройка проекта..."
# Создаем папку storage, если её нет, и выдаем права
mkdir -p storage/processed
chmod -R 777 storage

# Настраиваем переменную для нейросети
export U2NET_HOME=/tmp/.u2net

echo "=================================================="
echo "✅ Все зависимости установлены!"
echo "Запуск встроенного сервера PHP на порту 8000..."
echo "=================================================="

# Убиваем старый процесс на порту 8000, если он есть
fuser -k 8000/tcp 2>/dev/null

# Запускаем PHP сервер в фоне
nohup php -S 0.0.0.0:8000 > server.log 2>&1 &

# Пытаемся получить внешний или локальный IP
IP=$(curl -s -4 ifconfig.me)
if [ -z "$IP" ]; then
    IP=$(hostname -I | awk '{print $1}')
fi
if [ -z "$IP" ]; then
    IP="localhost"
fi

echo ""
echo "🎉 Готово! Проект успешно запущен."
echo "🌍 Ваш сайт доступен по адресу: http://$IP:8000"
echo "📄 Логи сервера сохраняются в файл: server.log"
echo ""
echo "Для остановки сервера выполните команду: fuser -k 8000/tcp"
