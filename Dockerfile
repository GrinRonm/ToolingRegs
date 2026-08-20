FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    python3-venv \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libsqlite3-dev \
    zip \
    unzip \
    curl \
    git \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_sqlite

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Setup Python environment
# We use PIP_BREAK_SYSTEM_PACKAGES to install globally in the container
ENV PIP_BREAK_SYSTEM_PACKAGES=1
RUN pip3 install --no-cache-dir \
    rembg==2.0.40 \
    pdf2docx \
    pdfplumber==0.7.3 \
    pandas \
    openpyxl \
    PyPDF2 \
    pillow

# Set U2NET_HOME for rembg models to avoid permission issues
ENV U2NET_HOME=/tmp/.u2net

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage

# Expose port
EXPOSE 80

# Pre-download rembg model (optional, but speeds up first use)
# RUN python3 -c "from rembg import remove; from PIL import Image; remove(Image.new('RGB', (10,10)))" || true
