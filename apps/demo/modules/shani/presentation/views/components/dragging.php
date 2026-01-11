<style>
    .board {
        display: flex;
        gap: 16px;
        max-width: 800px;
        margin: 32px auto;
        font-family: system-ui, sans-serif;
    }
    .zone {
        flex: 1;
        border: 2px dashed #999;
        border-radius: 8px;
        min-height: 200px;
        padding: 12px;
        background: #fafafa;
        transition: border-color 150ms, background 150ms;
    }
    .zone.over {
        border-color: #2a6df4;
        background: #f0f6ff;
    }
    .item {
        display: block;
        padding: 8px 12px;
        margin: 6px 0;
        background: #2a6df4;
        color: white;
        border-radius: 6px;
        cursor: grab;
        user-select: none;
        touch-action: none;
    }
    .item.dragging {
        opacity: 0.5;
    }
    .drag-ghost {
        position: fixed;
        z-index: 9999;
        opacity: 0.6;
        pointer-events: none;
        transform: translate(-50%, -50%);
    }
    .item:focus, .zone:focus {
        outline: 3px solid #ffbf47;
    }
</style>
<div class="board">
    <div class="zone" id="zone-a" tabindex="0" aria-dropeffect="none">
        <strong>Zone A</strong>
        <div class="item" id="card-1" tabindex="0" role="button" aria-grabbed="false">Card 1</div>
        <div class="item" id="card-2" tabindex="0" role="button" aria-grabbed="false">Card 2</div>
    </div>
    <div class="zone" id="zone-b" tabindex="0" aria-dropeffect="none">
        <strong>Zone B</strong>
        <div class="item" id="card-3" tabindex="0" role="button" aria-grabbed="false">Card 3</div>
    </div>
    <div class="zone" id="zone-c" tabindex="0" aria-dropeffect="none">
        <strong>Zone C</strong>
    </div>
</div>
<script>
    // --- Pointer Events drag logic (same as before, trimmed for clarity) ---
    const state = {item: null, ghost: null, from: null, offsetX: 0, offsetY: 0, lastZone: null, lastBefore: null, raf: null};
    function createGhost(el) {
        const ghost = el.cloneNode(true);
        const rect = el.getBoundingClientRect();
        ghost.classList.add('drag-ghost');
        ghost.style.width = rect.width + 'px';
        ghost.style.height = rect.height + 'px';
        document.body.appendChild(ghost);
        return ghost;
    }
    function zoneFromPoint(x, y) {
        const el = document.elementFromPoint(x, y);
        return el ? el.closest('.zone') : null;
    }
    function getInsertBefore(zone, y) {
        const items = [...zone.querySelectorAll('.item:not(.dragging)')];
        let candidate = null, best = Number.NEGATIVE_INFINITY;
        for (const child of items) {
            const box = child.getBoundingClientRect(); const offset = y - (box.top + box.height / 2);
            if (offset < 0 && offset > best) {
                best = offset;
                candidate = child;
            }
        }
        return candidate;
    }
    function scheduleMove(x, y) {
        if (state.raf)
            return;
        state.raf = requestAnimationFrame(() => {
            state.raf = null;
            moveGhost(x, y);
        });
    }
    function moveGhost(x, y) {
        if (!state.item || !state.ghost)
            return;
        state.ghost.style.left = x + 'px';
        state.ghost.style.top = y + 'px';
        const zone = zoneFromPoint(x, y);
        document.querySelectorAll('.zone').forEach(z => z.classList.toggle('over', z === zone));
        if (!zone)
            return;
        const before = getInsertBefore(zone, y);
        if (state.lastZone !== zone || state.lastBefore !== before) {
            if (before)
                zone.insertBefore(state.item, before);
            else
                zone.appendChild(state.item);
            state.lastZone = zone;
            state.lastBefore = before;
        }
    }
    function onPointerDown(e) {
        const item = e.currentTarget;
        e.preventDefault();
        const rect = item.getBoundingClientRect();
        state.offsetX = e.clientX - rect.left;
        state.offsetY = e.clientY - rect.top;
        item.setPointerCapture(e.pointerId);
        state.item = item;
        state.from = item.closest('.zone');
        state.ghost = createGhost(item);
        item.classList.add('dragging');
        item.setAttribute('aria-grabbed', 'true');
        window.addEventListener('pointermove', onPointerMove, {passive: false});
        window.addEventListener('pointerup', onPointerUp, {passive: true});
        scheduleMove(e.clientX, e.clientY);
    }
    function onPointerMove(e) {
        if (!state.item)
            return;
        e.preventDefault();
        scheduleMove(e.clientX, e.clientY);
    }
    function onPointerUp() {
        window.removeEventListener('pointermove', onPointerMove);
        window.removeEventListener('pointerup', onPointerUp);
        if (!state.item)
            return;
        state.item.classList.remove('dragging');
        state.item.setAttribute('aria-grabbed', 'false');
        if (state.ghost)
            state.ghost.remove();
        document.querySelectorAll('.zone').forEach(z => z.classList.remove('over'));
        state.item = state.ghost = state.from = null;
        state.lastZone = state.lastBefore = null;
        if (state.raf)
            cancelAnimationFrame(state.raf), state.raf = null;
    }
    document.querySelectorAll('.item').forEach(item => item.addEventListener('pointerdown', onPointerDown));

    // --- Keyboard accessibility ---
    let kbCarry = null;
    document.addEventListener('keydown', e => {
        const active = document.activeElement;
        if (e.key === ' ' && active.classList.contains('item')) { // Space picks up
            e.preventDefault();
            kbCarry = active;
            active.setAttribute('aria-grabbed', 'true');
            document.querySelectorAll('.zone').forEach(z => z.setAttribute('aria-dropeffect', 'move'));
        }
        if (e.key === 'Enter' && kbCarry && active.classList.contains('zone')) { // Enter drops
            e.preventDefault();
            active.appendChild(kbCarry);
            kbCarry.setAttribute('aria-grabbed', 'false');
            kbCarry = null;
            document.querySelectorAll('.zone').forEach(z => z.setAttribute('aria-dropeffect', 'none'));
        }
        if (e.key === 'Escape' && kbCarry) { // Escape cancels
            kbCarry.setAttribute('aria-grabbed', 'false');
            kbCarry = null;
            document.querySelectorAll('.zone').forEach(z => z.setAttribute('aria-dropeffect', 'none'));
        }
    });
</script>