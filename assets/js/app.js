/**
 * Instyment — Main Application JS
 * Search, category filtering, navigation
 */

document.addEventListener('DOMContentLoaded', () => {
    initSearch();
    initCategories();
});

/* ── Search ── */
function initSearch() {
    const input = document.getElementById('search-input');
    if (!input) return;

    let debounceTimer;

    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            filterTools(input.value.trim());
        }, 200);
    });

    // Keyboard shortcut: "/" to focus search
    document.addEventListener('keydown', (e) => {
        if (e.key === '/' && document.activeElement !== input && !isInputFocused()) {
            e.preventDefault();
            input.focus();
        }
        if (e.key === 'Escape' && document.activeElement === input) {
            input.value = '';
            input.blur();
            filterTools('');
        }
    });
}

function filterTools(query) {
    const cards = document.querySelectorAll('.tool-card');
    const noResults = document.getElementById('no-results');
    query = query.toLowerCase();
    let visible = 0;

    cards.forEach(card => {
        const name = (card.dataset.name || '').toLowerCase();
        const desc = (card.dataset.desc || '').toLowerCase();
        const tags = (card.dataset.tags || '').toLowerCase();
        const category = (card.dataset.category || '').toLowerCase();

        const match = !query
            || name.includes(query)
            || desc.includes(query)
            || tags.includes(query)
            || category.includes(query);

        // Also check active category filter
        const activeCategory = document.querySelector('.category-tab.active');
        const catFilter = activeCategory ? activeCategory.dataset.category : 'all';
        const catMatch = catFilter === 'all' || category === catFilter;

        if (match && catMatch) {
            card.classList.remove('hidden');
            card.style.animationDelay = `${visible * 0.03}s`;
            visible++;
        } else {
            card.classList.add('hidden');
        }
    });

    if (noResults) {
        noResults.style.display = visible === 0 ? 'block' : 'none';
    }
}

/* ── Category Tabs ── */
function initCategories() {
    const tabs = document.querySelectorAll('.category-tab');
    const searchInput = document.getElementById('search-input');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Toggle active state
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            // Apply filter
            filterTools(searchInput ? searchInput.value.trim() : '');
        });
    });
}

/* ── Helper: Check if any input/textarea is focused ── */
function isInputFocused() {
    const el = document.activeElement;
    return el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.isContentEditable);
}

/* ── Toast Notification ── */
function showToast(message, type = 'info', duration = 3000) {
    // Remove existing toast
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.add('show');
    });

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

/* ── Format file size ── */
function formatSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

/* ── Get CSRF token ── */
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

/* ── Force Download via Blob (bypasses browser extension hijackers) ── */
async function forceDownload(url, filename) {
    try {
        const resp = await fetch(url);
        if (!resp.ok) throw new Error('Download failed');
        const blob = await resp.blob();
        const objUrl = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = objUrl;
        a.download = filename || 'download';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(objUrl);
        a.remove();
    } catch (e) {
        // Fallback
        window.open(url, '_blank');
    }
}

/* ── Download Multiple as ZIP ── */
async function downloadAsZip(urls) {
    const files = urls.map(url => {
        const params = new URLSearchParams(url.split('?')[1]);
        return { id: params.get('id'), name: params.get('name') };
    }).filter(f => f.id);
    
    if (!files.length) return;
    
    showToast('Создание ZIP архива...', 'info');
    
    try {
        const resp = await fetch('/api/download-zip', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ files })
        });
        const data = await resp.json();
        if (data.success) {
            forceDownload(data.download_url, 'Архив_Instyment.zip');
        } else {
            showToast(data.error || 'Ошибка архивации', 'error');
        }
    } catch (e) {
        showToast('Ошибка сети при архивации', 'error');
    }
}
