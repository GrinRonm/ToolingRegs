/**
 * Instyment — Universal File Uploader
 * Drag & Drop, Ctrl+V (Clipboard API), file input, bulk upload
 *
 * Usage:
 *   const uploader = new Uploader({
 *       zone: '#upload-zone',
 *       fileInput: '#file-input',
 *       fileList: '#file-list',
 *       accepts: ['image/jpeg', 'image/png', 'image/webp'],
 *       maxFiles: 20,
 *       maxSize: 50 * 1024 * 1024,
 *       supportsPaste: true,
 *       onFilesChanged: (files) => { ... }
 *   });
 */

class Uploader {
    constructor(options = {}) {
        this.options = {
            zone: '#upload-zone',
            fileInput: '#file-input',
            fileList: '#file-list',
            accepts: [],
            maxFiles: 20,
            maxSize: 50 * 1024 * 1024,
            supportsPaste: true,
            multiple: true,
            showPreview: true,
            onFilesChanged: null,
            ...options
        };

        this.files = [];
        this.zone = document.querySelector(this.options.zone);
        this.fileInput = document.querySelector(this.options.fileInput);
        this.fileListEl = document.querySelector(this.options.fileList);

        if (!this.zone) return;

        this.init();
    }

    init() {
        // Drag & Drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            this.zone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        this.zone.addEventListener('dragenter', () => this.zone.classList.add('dragover'));
        this.zone.addEventListener('dragover', () => this.zone.classList.add('dragover'));
        this.zone.addEventListener('dragleave', () => this.zone.classList.remove('dragover'));
        this.zone.addEventListener('drop', (e) => {
            this.zone.classList.remove('dragover');
            const files = e.dataTransfer.files;
            this.addFiles(files);
        });

        // Click to open file dialog
        this.zone.addEventListener('click', () => {
            if (this.fileInput) this.fileInput.click();
        });

        // File input change
        if (this.fileInput) {
            this.fileInput.addEventListener('change', (e) => {
                this.addFiles(e.target.files);
                e.target.value = '';
            });
        }

        // Ctrl+V paste
        if (this.options.supportsPaste) {
            document.addEventListener('paste', (e) => {
                // Don't intercept if user is typing in a text field
                if (isInputFocused()) return;

                const items = e.clipboardData?.items;
                if (!items) return;

                const pastedFiles = [];
                for (let i = 0; i < items.length; i++) {
                    if (items[i].kind === 'file') {
                        const file = items[i].getAsFile();
                        if (file) pastedFiles.push(file);
                    }
                }

                if (pastedFiles.length > 0) {
                    e.preventDefault();
                    this.addFiles(pastedFiles);
                    showToast('Файл вставлен из буфера обмена', 'success');
                }
            });
        }
    }

    addFiles(fileList) {
        const filesArray = Array.from(fileList);

        for (const file of filesArray) {
            if (this.options.maxFiles === 1 && this.files.length >= 1) {
                // If maxFiles is 1, replace the current file instead of rejecting
                this.files = [];
            } else if (this.files.length >= this.options.maxFiles) {
                showToast(`Максимум ${this.options.maxFiles} файлов`, 'error');
                break;
            }

            // Check file size
            if (file.size > this.options.maxSize) {
                const maxMB = Math.round(this.options.maxSize / 1024 / 1024);
                showToast(`${file.name}: файл слишком большой (макс. ${maxMB}MB)`, 'error');
                continue;
            }

            // Check MIME type
            if (this.options.accepts.length > 0 && !this.options.accepts.includes(file.type)) {
                showToast(`${file.name}: неподдерживаемый формат`, 'error');
                continue;
            }

            // Check duplicates
            if (this.files.some(f => f.name === file.name && f.size === file.size)) {
                continue;
            }

            this.files.push(file);
        }

        this.renderFileList();
        this.notify();
    }

    removeFile(index) {
        this.files.splice(index, 1);
        this.renderFileList();
        this.notify();
    }

    clear() {
        this.files = [];
        this.renderFileList();
        this.notify();
    }

    renderFileList() {
        if (!this.fileListEl) return;

        if (this.files.length === 0) {
            this.fileListEl.innerHTML = '';
            this.zone.style.display = '';
            return;
        }

        // Hide zone when files are added (optional — can keep visible)
        // this.zone.style.display = 'none';

        let html = '';
        this.files.forEach((file, index) => {
            const isImage = file.type.startsWith('image/');
            html += `
                <div class="file-item" data-index="${index}">
                    ${isImage && this.options.showPreview
                        ? `<img class="file-item-preview" src="${URL.createObjectURL(file)}" alt="">`
                        : `<div class="file-item-preview" style="display:flex;align-items:center;justify-content:center;font-size:1.3rem;">📄</div>`
                    }
                    <div class="file-item-info">
                        <div class="file-item-name">${this.escapeHtml(file.name)}</div>
                        <div class="file-item-size">${formatSize(file.size)}</div>
                    </div>
                    <button type="button" class="file-item-remove" data-index="${index}" title="Удалить">✕</button>
                </div>
            `;
        });

        this.fileListEl.innerHTML = html;

        // Bind remove buttons
        this.fileListEl.querySelectorAll('.file-item-remove').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.removeFile(parseInt(btn.dataset.index));
            });
        });
    }

    notify() {
        if (typeof this.options.onFilesChanged === 'function') {
            this.options.onFilesChanged(this.files);
        }
    }

    getFiles() {
        return this.files;
    }

    escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
}
