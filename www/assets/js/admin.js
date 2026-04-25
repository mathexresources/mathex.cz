'use strict';

/* ─── Flash auto-dismiss ────────────────────────────────────────────────────── */
document.querySelectorAll('.flash').forEach(el => {
    setTimeout(() => {
        el.style.transition = 'opacity 400ms';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 420);
    }, 4500);
});

/* ─── Mobile sidebar toggle ─────────────────────────────────────────────────── */
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar       = document.querySelector('.admin-sidebar');
if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
        const open = sidebar.classList.toggle('open');
        sidebarToggle.setAttribute('aria-expanded', String(open));
    });
    document.addEventListener('click', e => {
        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
            sidebar.classList.remove('open');
            if (sidebarToggle) sidebarToggle.setAttribute('aria-expanded', 'false');
        }
    });
}

/* ─── Active nav link ────────────────────────────────────────────────────────── */
document.querySelectorAll('.admin-nav__link').forEach(link => {
    if (link.href === window.location.href) link.classList.add('active');
});

/* ─── Bulk actions confirm ──────────────────────────────────────────────────── */
function confirmBulk() {
    const action = document.querySelector('[name="bulk_action"]')?.value;
    if (action === 'delete') return confirm('Opravdu smazat vybrané záznamy? Tato akce je nevratná.');
    return true;
}

/* ─── Select-all checkbox ────────────────────────────────────────────────────── */
const selectAll = document.getElementById('selectAll');
if (selectAll) {
    selectAll.addEventListener('change', function() {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
    });
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.addEventListener('change', () => {
            const all   = document.querySelectorAll('.row-checkbox').length;
            const checked = document.querySelectorAll('.row-checkbox:checked').length;
            selectAll.indeterminate = checked > 0 && checked < all;
            selectAll.checked = checked === all;
        });
    });
}

/* ─── Delete confirmation ────────────────────────────────────────────────────── */
document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
        if (!confirm(el.dataset.confirm || 'Opravdu chcete pokračovat?')) e.preventDefault();
    });
});

/* ─── Skill slider live preview ─────────────────────────────────────────────── */
document.querySelectorAll('.skill-slider-wrap input[type=range]').forEach(slider => {
    const output = slider.closest('.skill-slider-wrap')?.querySelector('.skill-slider-value');
    if (output) {
        slider.addEventListener('input', () => { output.textContent = slider.value + '%'; });
    }
});

/* ─── Editor tabs ────────────────────────────────────────────────────────────── */
document.querySelectorAll('.editor-tab-bar').forEach(bar => {
    bar.querySelectorAll('.editor-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            bar.querySelectorAll('.editor-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const wrap = tab.closest('.editor-tabs');
            if (wrap) {
                wrap.querySelectorAll('.editor-tab-content').forEach(c => {
                    c.style.display = c.dataset.content === target ? '' : 'none';
                });
            }
        });
    });
});

/* ─── Drag & drop table rows (sortable order) ───────────────────────────────── */
let dragSrc = null;
document.querySelectorAll('[draggable="true"]').forEach(row => {
    row.addEventListener('dragstart', e => { dragSrc = row; row.style.opacity = '0.5'; });
    row.addEventListener('dragend',   () => { dragSrc = null; row.style.opacity = ''; });
    row.addEventListener('dragover',  e => { e.preventDefault(); });
    row.addEventListener('drop', e => {
        e.preventDefault();
        if (dragSrc && dragSrc !== row) {
            const tbody = row.closest('tbody');
            const rows  = [...tbody.querySelectorAll('tr[draggable]')];
            const srcIdx = rows.indexOf(dragSrc);
            const dstIdx = rows.indexOf(row);
            if (srcIdx < dstIdx) row.after(dragSrc);
            else row.before(dragSrc);
        }
    });
});

/* ─── Image preview on file upload ─────────────────────────────────────────── */
document.querySelectorAll('input[type=file][data-preview]').forEach(input => {
    input.addEventListener('change', () => {
        const preview = document.getElementById(input.dataset.preview);
        if (!preview || !input.files?.[0]) return;
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    });
});

/* ─── Tech tags input helper ────────────────────────────────────────────────── */
const techTagInput = document.getElementById('techTagInput');
if (techTagInput) {
    const hiddenField = document.getElementById('technologies');
    let tags = [];
    try { tags = JSON.parse(hiddenField?.value || '[]'); } catch {}

    function renderTags() {
        const container = document.getElementById('techTagsContainer');
        if (!container) return;
        container.innerHTML = '';
        tags.forEach((tag, i) => {
            const span = document.createElement('span');
            span.className = 'tech-tag';
            span.innerHTML = `${tag} <button type="button" data-i="${i}" style="background:none;border:none;color:inherit;cursor:pointer;margin-left:4px;">×</button>`;
            span.querySelector('button').addEventListener('click', () => {
                tags.splice(i, 1);
                if (hiddenField) hiddenField.value = JSON.stringify(tags);
                renderTags();
            });
            container.appendChild(span);
        });
    }

    techTagInput.addEventListener('keydown', e => {
        if ((e.key === 'Enter' || e.key === ',') && techTagInput.value.trim()) {
            e.preventDefault();
            const val = techTagInput.value.trim().replace(',', '');
            if (val && !tags.includes(val)) {
                tags.push(val);
                if (hiddenField) hiddenField.value = JSON.stringify(tags);
                renderTags();
            }
            techTagInput.value = '';
        }
    });

    renderTags();
}
