@extends('layouts.app')
@section('title', 'Input Transaksi')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('sales.index') }}" class="text-[#7A8AA8] hover:text-[#0A2540]">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <h2 class="text-lg font-semibold">Input Transaksi Baru</h2>
</div>

<livewire:sale-form />

<script>
function syncMoney(displayEl, hiddenId) {
    var raw = parseInt(displayEl.value.replace(/[^0-9]/g, ''), 10) || 0;
    displayEl.value = raw > 0 ? raw.toLocaleString('id-ID') : '';
    var hidden = document.getElementById(hiddenId);
    if (hidden) {
        hidden.value = raw;
        hidden.dispatchEvent(new Event('input', { bubbles: true }));
    }
}

// ── Searchable Select ──────────────────────────────────────────────────────
(function () {
    // Track body-mounted panels so we can clean up orphans on Livewire re-renders
    var panelMap = new Map(); // ss-wrap element → body panel element

    function buildSS(wrap) {
        if (wrap.querySelector('.ss-display')) return;

        var placeholder = wrap.dataset.placeholder || 'Pilih...';
        var hidden      = document.getElementById(wrap.dataset.target);

        var opts = [];
        wrap.querySelectorAll('.ss-opts span').forEach(function (s) {
            opts.push({ value: s.dataset.v, label: s.textContent.trim() });
        });

        function labelFor(val) {
            if (!val) return placeholder;
            var o = opts.find(function (x) { return x.value == val; });
            return o ? o.label : placeholder;
        }

        // Trigger button (stays inside the card, doesn't affect layout)
        var display = document.createElement('div');
        display.className = 'ss-display field-input';
        display.style.cssText = 'cursor:pointer;display:flex;align-items:center;gap:8px;user-select:none;padding-right:10px';
        display.setAttribute('tabindex', '0');

        var lbl = document.createElement('span');
        lbl.className = 'ss-label';
        lbl.style.cssText = 'flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap';
        var cur = hidden ? hidden.value : '';
        lbl.textContent = labelFor(cur);
        lbl.style.color = cur ? 'var(--ink)' : 'var(--ink-mute)';

        var chev = document.createElement('span');
        chev.className = 'ss-chev';
        chev.style.cssText = 'flex-shrink:0;display:flex;align-items:center;color:var(--ink-mute);transition:transform .15s';
        chev.innerHTML = '<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';

        display.appendChild(lbl);
        display.appendChild(chev);
        wrap.appendChild(display);

        // Panel mounted on body — bypasses overflow:hidden on any ancestor
        var panel = document.createElement('div');
        panel.style.cssText = 'display:none;position:fixed;z-index:9999;background:#fff;border:1px solid var(--line);border-radius:10px;box-shadow:0 8px 28px rgba(10,37,64,.16);overflow:hidden';
        document.body.appendChild(panel);
        panelMap.set(wrap, panel);

        var search = document.createElement('input');
        search.type = 'text';
        search.placeholder = 'Cari...';
        search.style.cssText = 'width:100%;padding:10px 14px;border:none;border-bottom:1px solid var(--line);outline:none;font-size:13px;color:var(--ink);box-sizing:border-box;background:#fff';

        var list = document.createElement('div');
        list.style.cssText = 'max-height:240px;overflow-y:auto';

        function renderList(q) {
            list.innerHTML = '';
            var filtered = q
                ? opts.filter(function (o) { return o.label.toLowerCase().includes(q.toLowerCase()); })
                : opts;

            var none = document.createElement('div');
            none.className = 'ss-item';
            none.dataset.val = '';
            none.textContent = placeholder;
            none.style.cssText = 'padding:9px 14px;font-size:13px;cursor:pointer;color:var(--ink-mute)';
            list.appendChild(none);

            filtered.forEach(function (opt) {
                var item = document.createElement('div');
                item.className = 'ss-item';
                item.dataset.val = opt.value;
                item.textContent = opt.label;
                var selected = hidden && hidden.value == opt.value;
                item.style.cssText = 'padding:9px 14px;font-size:13px;cursor:pointer;color:var(--ink)'
                    + (selected ? ';background:var(--bg-soft);font-weight:600' : '');
                list.appendChild(item);
            });

            if (!filtered.length) {
                var empty = document.createElement('div');
                empty.style.cssText = 'padding:12px 14px;font-size:13px;color:var(--ink-mute);text-align:center';
                empty.textContent = 'Tidak ada hasil';
                list.appendChild(empty);
            }
        }

        function reposition() {
            var rect = display.getBoundingClientRect();
            panel.style.top   = (rect.bottom + 4) + 'px';
            panel.style.left  = rect.left + 'px';
            panel.style.width = rect.width + 'px';
        }

        function open() {
            renderList('');
            search.value = '';
            reposition();
            panel.style.display = 'block';
            chev.style.transform = 'rotate(180deg)';
            setTimeout(function () { search.focus(); }, 10);
        }

        function close() {
            panel.style.display = 'none';
            chev.style.transform = '';
        }

        display.addEventListener('click', function () {
            panel.style.display === 'none' ? open() : close();
        });
        display.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); }
        });

        search.addEventListener('input', function () { renderList(this.value); });
        search.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') close();
        });

        list.addEventListener('click', function (e) {
            var item = e.target.closest('.ss-item');
            if (!item) return;
            var val = item.dataset.val || '';
            lbl.textContent = labelFor(val);
            lbl.style.color = val ? 'var(--ink)' : 'var(--ink-mute)';
            if (hidden) {
                hidden.value = val;
                hidden.dispatchEvent(new Event('input', { bubbles: true }));
            }
            close();
        });

        list.addEventListener('mouseover', function (e) {
            var item = e.target.closest('.ss-item');
            if (item) item.style.background = 'var(--bg-soft)';
        });
        list.addEventListener('mouseout', function (e) {
            var item = e.target.closest('.ss-item');
            if (item && (!hidden || hidden.value != item.dataset.val || !item.dataset.val))
                item.style.background = '';
        });

        // Close on outside click (panel is in body, so check both)
        document.addEventListener('click', function (e) {
            if (!display.contains(e.target) && !panel.contains(e.target)) close();
        });

        // Close and reposition on scroll/resize
        window.addEventListener('scroll', close, true);
        window.addEventListener('resize', close);

        panel.appendChild(search);
        panel.appendChild(list);
    }

    function syncLabel(wrap) {
        var hidden = document.getElementById(wrap.dataset.target);
        if (!hidden) return;
        var lbl = wrap.querySelector('.ss-label');
        if (!lbl) { buildSS(wrap); return; }
        var val = hidden.value;
        if (val) {
            var opt = wrap.querySelector('.ss-opts span[data-v="' + val + '"]');
            lbl.textContent = opt ? opt.textContent.trim() : (wrap.dataset.placeholder || 'Pilih...');
            lbl.style.color = 'var(--ink)';
        } else {
            lbl.textContent = wrap.dataset.placeholder || 'Pilih...';
            lbl.style.color = 'var(--ink-mute)';
        }
    }

    function cleanOrphans() {
        panelMap.forEach(function (panel, wrap) {
            if (!document.contains(wrap)) {
                panel.remove();
                panelMap.delete(wrap);
            }
        });
    }

    function initAll() {
        document.querySelectorAll('.ss-wrap').forEach(buildSS);
    }

    document.addEventListener('DOMContentLoaded', initAll);
    document.addEventListener('livewire:updated', function () {
        cleanOrphans();
        document.querySelectorAll('.ss-wrap').forEach(function (wrap) {
            if (wrap.querySelector('.ss-display')) syncLabel(wrap);
            else buildSS(wrap);
        });
    });
})();
</script>
@endsection

