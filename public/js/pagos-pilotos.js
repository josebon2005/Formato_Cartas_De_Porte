(() => {
    'use strict';

    const form = document.querySelector('[data-pago-form]');
    if (!form) return;

    const viajes = form.querySelector('[data-viajes]');
    const movimientos = form.querySelector('[data-movimientos]');
    let nextViaje = nextIndex(viajes, 'viajes');
    let nextMovimiento = nextIndex(movimientos, 'movimientos');
    let dirty = false;

    function nextIndex(container, name) {
        if (!container) return 0;
        const pattern = new RegExp('^' + name + '\\[(\\d+)\\]');
        return [...container.querySelectorAll('[name]')].reduce((largest, input) => {
            const match = input.name.match(pattern);
            return match ? Math.max(largest, Number(match[1]) + 1) : largest;
        }, 0);
    }

    // Parse and add integer cents; no binary floating-point money arithmetic.
    function cents(value) {
        const match = String(value ?? '').trim().match(/^(\d+)(?:\.(\d{0,2}))?$/);
        return match ? BigInt(match[1]) * 100n + BigInt((match[2] || '').padEnd(2, '0')) : 0n;
    }

    function money(value) {
        const absolute = value < 0n ? -value : value;
        const whole = String(absolute / 100n).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return 'Q. ' + (value < 0n ? '−' : '') + whole + '.' + String(absolute % 100n).padStart(2, '0');
    }

    function updateTotals() {
        let totalViajes = 0n;
        let ingresos = 0n;
        let descuentos = 0n;
        viajes?.querySelectorAll('[data-viaje-valor]').forEach(input => totalViajes += cents(input.value));
        viajes?.querySelectorAll('[data-viaje-fecha]').forEach(input => {
            const date = input.value.match(/^(\d{4})-(\d{2})-(\d{2})$/);
            input.parentElement.querySelector('[data-viaje-fecha-legible]').textContent = date ? date[3] + '-' + date[2] + '-' + date[1] : 'dd-mm-aaaa';
        });
        movimientos?.querySelectorAll('[data-movimiento-row]').forEach(row => {
            const apply = row.querySelector('[data-movimiento-aplicar]').checked;
            row.classList.toggle('pagos-disabled', !apply);
            if (!apply) return;
            const value = cents(row.querySelector('[data-movimiento-valor]').value);
            if (row.querySelector('[data-movimiento-tipo]').value === 'DESCUENTO') descuentos += value;
            else ingresos += value;
        });
        const sueldo = cents(form.querySelector('[data-sueldo]')?.value);
        const totals = {viajes: totalViajes, sueldo, ingresos, descuentos, pagar: totalViajes + sueldo + ingresos - descuentos};
        form.querySelectorAll('[data-total]').forEach(output => output.textContent = money(totals[output.dataset.total]));
        const emptyViajes = form.querySelector('[data-viajes-empty]');
        const emptyMovimientos = form.querySelector('[data-movimientos-empty]');
        if (emptyViajes) emptyViajes.hidden = Boolean(viajes?.children.length);
        if (emptyMovimientos) emptyMovimientos.hidden = Boolean(movimientos?.children.length);
    }

    function addRow(templateId, container, index) {
        const template = document.getElementById(templateId);
        if (!template || !container) return;
        const content = template.content.cloneNode(true);
        content.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replaceAll('__INDEX__', String(index));
            if (input.value === '__INDEX__') input.value = String(index);
        });
        const row = content.querySelector('tr');
        container.appendChild(content);
        dirty = true;
        updateTotals();
        row.querySelector('input:not([type="hidden"]), select')?.focus();
    }

    form.addEventListener('click', event => {
        if (event.target.closest('[data-add-viaje]')) addRow('pago-viaje-template', viajes, nextViaje++);
        if (event.target.closest('[data-add-movimiento]')) addRow('pago-movimiento-template', movimientos, nextMovimiento++);
        const remove = event.target.closest('[data-remove-row]');
        if (remove) {
            const row = remove.closest('tr');
            const container = row.parentElement;
            const nextRow = row.nextElementSibling || row.previousElementSibling;
            row.remove();
            dirty = true;
            updateTotals();
            (nextRow?.querySelector('[data-remove-row]') || form.querySelector(container === viajes ? '[data-add-viaje]' : '[data-add-movimiento]'))?.focus();
        }
    });

    ['input', 'change'].forEach(type => form.addEventListener(type, () => {
        dirty = true;
        updateTotals();
    }));

    function serializeCollection(container, name, rowSelector) {
        if (!container) return;
        const rows = [...container.querySelectorAll(rowSelector)].map((row, order) => {
            const data = {};
            row.querySelectorAll('[name]').forEach(input => {
                if (input.type === 'checkbox' && !input.checked) return;
                const key = input.name.match(/\[([^\]]+)\]$/)?.[1];
                if (key) data[key] = input.value;
            });
            data.orden = order;
            return data;
        });
        const serialized = document.createElement('input');
        serialized.type = 'hidden';
        serialized.name = name + '_json';
        serialized.value = JSON.stringify(rows);
        serialized.dataset.paymentJson = '1';
        form.appendChild(serialized);
        form.querySelectorAll('[name]').forEach(input => {
            if (input.name === name || input.name.startsWith(name + '[')) {
                input.disabled = true;
                input.dataset.paymentSerialized = '1';
            }
        });
    }

    form.addEventListener('submit', () => {
        // One field per collection avoids PHP's max_input_vars truncation on busy months.
        serializeCollection(viajes, 'viajes', '[data-viaje-row]');
        serializeCollection(movimientos, 'movimientos', '[data-movimiento-row]');
        dirty = false;
    });
    window.addEventListener('pageshow', () => {
        // Restore controls if the browser returns to this page from its back/forward cache.
        form.querySelectorAll('[data-payment-serialized]').forEach(input => {
            input.disabled = false;
            delete input.dataset.paymentSerialized;
        });
        form.querySelectorAll('[data-payment-json]').forEach(input => input.remove());
    });
    document.querySelector('[data-periodo-form]')?.addEventListener('submit', event => {
        if (dirty && !window.confirm('¿Cargar los viajes de este período? Se perderán los cambios de este pago que aún no ha guardado.')) {
            event.preventDefault();
        } else {
            dirty = false;
        }
    });
    window.addEventListener('beforeunload', event => {
        if (!dirty) return;
        event.preventDefault();
        event.returnValue = '';
    });
    updateTotals();
})();
