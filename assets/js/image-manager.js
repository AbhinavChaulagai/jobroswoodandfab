/**
 * image-manager.js — Admin product image manager.
 * Handles drag-and-drop upload, URL add, reorder, and remove.
 */
'use strict';

(function () {

    /* ── State ──────────────────────────────────────────────────────────────── */
    let images  = Array.isArray(window.EXISTING_IMAGES) ? [...window.EXISTING_IMAGES] : [];
    let dragSrc = null; // index of item being dragged

    /* ── DOM refs ───────────────────────────────────────────────────────────── */
    const list          = document.getElementById('imgList');
    const countBadge    = document.getElementById('imgCount');
    const jsonInput     = document.getElementById('imagesJson');
    const fileInput     = document.getElementById('fileInput');
    const browseBtn     = document.getElementById('browseBtn');
    const dropZone      = document.getElementById('dropZone');
    const dropInner     = document.getElementById('dropZoneInner');
    const uploadProgress= document.getElementById('uploadProgress');
    const progressFill  = document.getElementById('progressFill');
    const progressLabel = document.getElementById('progressLabel');
    const urlInput      = document.getElementById('urlInput');
    const addUrlBtn     = document.getElementById('addUrlBtn');
    const form          = document.getElementById('productForm');

    /* ── Render list ────────────────────────────────────────────────────────── */
    function render() {
        list.innerHTML = '';
        countBadge.textContent = images.length;
        jsonInput.value = JSON.stringify(images);

        images.forEach(function (src, idx) {
            const li = document.createElement('li');
            li.className = 'img-item';
            li.setAttribute('draggable', 'true');
            li.dataset.index = idx;

            // Resolve preview URL (filename → placehold.co fallback)
            const previewSrc = /^https?:\/\//.test(src)
                ? src
                : '/assets/images/' + src;

            li.innerHTML = `
                <span class="img-drag-handle" title="Drag to reorder">&#8597;</span>
                <img class="img-thumb" src="${escHtml(previewSrc)}"
                     alt="Image ${idx + 1}"
                     onerror="this.src='https://placehold.co/80x60/8B6343/F5ECD7?text=img'"
                     width="80" height="60">
                <span class="img-label" title="${escHtml(src)}">${escHtml(truncate(src, 40))}</span>
                <span class="img-badge${idx === 0 ? ' primary' : ''}">${idx === 0 ? 'Primary' : '#' + (idx + 1)}</span>
                <button type="button" class="img-remove-btn" data-index="${idx}" title="Remove image">&times;</button>
            `;

            // Drag events
            li.addEventListener('dragstart', onDragStart);
            li.addEventListener('dragover',  onDragOver);
            li.addEventListener('dragleave', onDragLeave);
            li.addEventListener('drop',      onDrop);
            li.addEventListener('dragend',   onDragEnd);

            // Remove button
            li.querySelector('.img-remove-btn').addEventListener('click', function () {
                images.splice(idx, 1);
                render();
            });

            list.appendChild(li);
        });
    }

    /* ── Drag-and-drop reorder ──────────────────────────────────────────────── */
    function onDragStart(e) {
        dragSrc = parseInt(this.dataset.index);
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
    }

    function onDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        this.classList.add('drag-over');
    }

    function onDragLeave() {
        this.classList.remove('drag-over');
    }

    function onDrop(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
        const target = parseInt(this.dataset.index);
        if (dragSrc === null || dragSrc === target) return;
        const moved = images.splice(dragSrc, 1)[0];
        images.splice(target, 0, moved);
        render();
    }

    function onDragEnd() {
        this.classList.remove('dragging');
        document.querySelectorAll('.img-item').forEach(el => el.classList.remove('drag-over'));
        dragSrc = null;
    }

    /* ── File upload via drop zone ──────────────────────────────────────────── */
    browseBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => uploadFiles(fileInput.files));

    dropZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        dropZone.classList.add('drop-active');
    });
    dropZone.addEventListener('dragleave', function () {
        dropZone.classList.remove('drop-active');
    });
    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropZone.classList.remove('drop-active');
        if (e.dataTransfer.files.length) uploadFiles(e.dataTransfer.files);
    });

    async function uploadFiles(files) {
        const fileArr = Array.from(files).filter(f => f.type.startsWith('image/'));
        if (!fileArr.length) return;

        dropInner.hidden = true;
        uploadProgress.hidden = false;

        for (let i = 0; i < fileArr.length; i++) {
            const pct = Math.round(((i) / fileArr.length) * 100);
            progressFill.style.width = pct + '%';
            progressLabel.textContent = `Uploading ${i + 1} of ${fileArr.length}…`;

            const url = await uploadOne(fileArr[i]);
            if (url) {
                images.push(url);
                render();
            }
        }

        progressFill.style.width = '100%';
        progressLabel.textContent = 'Done!';
        await delay(600);

        dropInner.hidden = false;
        uploadProgress.hidden = true;
        progressFill.style.width = '0%';
        fileInput.value = '';
    }

    async function uploadOne(file) {
        const fd = new FormData();
        fd.append('image', file);
        fd.append('csrf_token', window.CSRF_TOKEN || '');

        try {
            const res = await fetch(window.UPLOAD_ENDPOINT, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success && data.url) return data.url;
            alert('Upload failed: ' + (data.error || 'Unknown error'));
        } catch (err) {
            alert('Upload error: ' + err.message);
        }
        return null;
    }

    /* ── Add by URL ─────────────────────────────────────────────────────────── */
    function addUrl() {
        const val = urlInput.value.trim();
        if (!val) return;
        images.push(val);
        render();
        urlInput.value = '';
    }

    addUrlBtn.addEventListener('click', addUrl);
    urlInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); addUrl(); }
    });

    /* ── Sync hidden input before form submit ───────────────────────────────── */
    form.addEventListener('submit', function () {
        jsonInput.value = JSON.stringify(images);
    });

    /* ── Utils ──────────────────────────────────────────────────────────────── */
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function truncate(str, max) {
        return str.length > max ? '…' + str.slice(-(max - 1)) : str;
    }

    function delay(ms) {
        return new Promise(r => setTimeout(r, ms));
    }

    /* ── Init ───────────────────────────────────────────────────────────────── */
    render();

})();
