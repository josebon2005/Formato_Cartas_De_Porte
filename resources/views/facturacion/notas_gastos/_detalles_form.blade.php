@php
    $rows = collect(old('detalles', $detalles ?? $notaGasto->detalles->map(fn ($detalle) => [
        'concepto_gasto_id' => $detalle->concepto_gasto_id,
        'concepto_nombre' => $detalle->concepto_nombre,
        'numero_factura' => $detalle->numero_factura,
        'precio_unitario' => $detalle->precio_unitario,
        'cantidad' => $detalle->cantidad,
        'grupo' => $detalle->grupo,
        'incluido' => $detalle->incluido,
        'orden' => $detalle->orden,
    ])->all()));

    $formatCantidad = function ($value): string {
        if ($value === null || $value === '') {
            return '';
        }

        if (! is_numeric($value)) {
            return (string) $value;
        }

        $formatted = rtrim(rtrim(number_format((float) $value, 6, '.', ''), '0'), '.');

        return $formatted === '' || $formatted === '-0' ? '0' : $formatted;
    };
@endphp

<div class="span-3">
    <label class="description-heading" for="descripcion">Descripción de facturación</label>
    <textarea id="descripcion" name="descripcion" rows="5">{{ "\n".old('descripcion', $descripcion ?? $notaGasto->descripcion ?? '') }}</textarea>
    @error('descripcion') <div class="error">{{ $message }}</div> @enderror
    <div class="actions" style="margin-top: 8px;">
        <button class="btn secondary small" type="button" data-regenerate-description
            data-url="{{ isset($notaGasto) ? route('facturacion.notas-gastos.descripcion', $notaGasto) : route('facturacion.notas-gastos.descripcion-desde-carta', $cartaPorte) }}">REGENERAR DESCRIPCIÓN</button>
        <span class="subtle" data-description-status role="status" aria-live="polite"></span>
    </div>
    <dialog data-description-dialog aria-labelledby="description-dialog-title" style="max-width: 480px; width: calc(100% - 32px); padding: 24px; border: 1px solid var(--line); border-radius: 12px; background: var(--surface); color: var(--text);">
        <h2 id="description-dialog-title" style="margin-top: 0;">Regenerar descripción</h2>
        <p>¿Desea regenerar la descripción? Se reemplazarán los cambios realizados manualmente.</p>
        <div class="actions">
            <button class="btn secondary" type="button" data-description-cancel>Cancelar</button>
            <button class="btn accent" type="button" data-description-confirm>Regenerar</button>
        </div>
    </dialog>
</div>

<div class="span-3 expense-toolbar">
    <button class="btn secondary small" type="button" data-add-custom-expense>+ Agregar cobro solo para esta nota</button>
</div>

<div class="span-3 table-wrap">
    <table>
        <thead>
            <tr>
                <th>Usar</th>
                <th>Concepto</th>
                <th>Numero de factura</th>
                <th>Grupo</th>
                <th>Precio unitario</th>
                <th>Cantidad</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody data-expense-rows data-next-index="{{ $rows->count() }}">
            @forelse ($rows as $index => $detalle)
                @php
                    $incluido = (bool) ($detalle['incluido'] ?? false);
                    $precio = (float) ($detalle['precio_unitario'] ?? 0);
                    $cantidad = (float) ($detalle['cantidad'] ?? 1);
                @endphp
                <tr data-expense-row>
                    <td>
                        <input name="detalles[{{ $index }}][incluido]" type="hidden" value="0">
                        <input
                            name="detalles[{{ $index }}][incluido]"
                            type="checkbox"
                            value="1"
                            data-row-enabled
                            {{ $incluido ? 'checked' : '' }}
                            style="min-height: auto; width: auto;"
                        >
                    </td>
                    <td>
                        <input name="detalles[{{ $index }}][concepto_gasto_id]" type="hidden" value="{{ $detalle['concepto_gasto_id'] ?? '' }}">
                        <input name="detalles[{{ $index }}][orden]" type="hidden" value="{{ $detalle['orden'] ?? $index }}">
                        <input name="detalles[{{ $index }}][concepto_nombre]" required value="{{ $detalle['concepto_nombre'] ?? '' }}">
                        @error("detalles.$index.concepto_nombre") <div class="error">{{ $message }}</div> @enderror
                    </td>
                    <td>
                        <input name="detalles[{{ $index }}][numero_factura]" value="{{ $detalle['numero_factura'] ?? '' }}">
                        @error("detalles.$index.numero_factura") <div class="error">{{ $message }}</div> @enderror
                    </td>
                    <td>
                        <select name="detalles[{{ $index }}][grupo]" data-row-group>
                            <option value="subtotal" @selected(($detalle['grupo'] ?? 'subtotal') === 'subtotal')>Subtotal</option>
                            <option value="adicional" @selected(($detalle['grupo'] ?? 'subtotal') === 'adicional')>Adicional</option>
                        </select>
                    </td>
                    <td>
                        <input name="detalles[{{ $index }}][precio_unitario]" type="number" min="0" step="0.01" value="{{ number_format($precio, 2, '.', '') }}" data-row-price>
                        @error("detalles.$index.precio_unitario") <div class="error">{{ $message }}</div> @enderror
                    </td>
                    <td>
                        <input name="detalles[{{ $index }}][cantidad]" type="number" min="0" step="0.01" value="{{ $formatCantidad($detalle['cantidad'] ?? 1) }}" data-row-quantity>
                        @error("detalles.$index.cantidad") <div class="error">{{ $message }}</div> @enderror
                    </td>
                    <td><strong data-row-total>Q0.00</strong></td>
                </tr>
            @empty
                <tr data-empty-expenses>
                    <td colspan="7" class="empty">No hay conceptos activos. Agrega conceptos de gasto antes de generar notas.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6">Subtotal</th>
                <th data-subtotal>Q0.00</th>
            </tr>
            <tr>
                <th colspan="6">Total</th>
                <th data-total>Q0.00</th>
            </tr>
        </tfoot>
    </table>
</div>

@push('scripts')
    <script>
        (() => {
            const money = value => `Q${Number(value || 0).toLocaleString('es-GT', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })}`;
            const rowsBody = document.querySelector('[data-expense-rows]');
            const addCustomButton = document.querySelector('[data-add-custom-expense]');

            const recalculate = () => {
                let subtotal = 0;
                let adicional = 0;

                document.querySelectorAll('[data-expense-row]').forEach(row => {
                    const enabled = row.querySelector('[data-row-enabled]')?.checked;
                    const price = parseFloat(row.querySelector('[data-row-price]')?.value || '0');
                    const quantity = parseFloat(row.querySelector('[data-row-quantity]')?.value || '0');
                    const group = row.querySelector('[data-row-group]')?.value || 'subtotal';
                    const total = enabled ? price * quantity : 0;

                    if (group === 'adicional') {
                        adicional += total;
                    } else {
                        subtotal += total;
                    }

                    const totalCell = row.querySelector('[data-row-total]');

                    if (totalCell) {
                        totalCell.textContent = money(total);
                    }
                });

                const subtotalCell = document.querySelector('[data-subtotal]');
                const totalCell = document.querySelector('[data-total]');

                if (subtotalCell) {
                    subtotalCell.textContent = money(subtotal);
                }

                if (totalCell) {
                    totalCell.textContent = money(subtotal + adicional);
                }
            };

            const addCustomExpense = () => {
                const name = (window.prompt('Nombre del cobro') || '').trim();

                if (! name || ! rowsBody) {
                    return;
                }

                const index = Number(rowsBody.dataset.nextIndex || document.querySelectorAll('[data-expense-row]').length);
                rowsBody.dataset.nextIndex = String(index + 1);

                const row = document.createElement('tr');
                row.setAttribute('data-expense-row', '');
                row.innerHTML = `
                    <td>
                        <input name="detalles[${index}][incluido]" type="hidden" value="0">
                        <input name="detalles[${index}][incluido]" type="checkbox" value="1" data-row-enabled checked style="min-height: auto; width: auto;">
                    </td>
                    <td>
                        <input name="detalles[${index}][concepto_gasto_id]" type="hidden" value="">
                        <input name="detalles[${index}][orden]" type="hidden" value="${1000 + index}">
                        <input name="detalles[${index}][concepto_nombre]" required data-row-name>
                    </td>
                    <td>
                        <input name="detalles[${index}][numero_factura]" value="">
                    </td>
                    <td>
                        <select name="detalles[${index}][grupo]" data-row-group>
                            <option value="subtotal" selected>Subtotal</option>
                            <option value="adicional">Adicional</option>
                        </select>
                    </td>
                    <td>
                        <input name="detalles[${index}][precio_unitario]" type="number" min="0" step="0.01" value="0.00" data-row-price>
                    </td>
                    <td>
                        <input name="detalles[${index}][cantidad]" type="number" min="0" step="0.01" value="1" data-row-quantity>
                    </td>
                    <td><strong data-row-total>Q0.00</strong></td>
                `;
                row.querySelector('[data-row-name]').value = name;

                rowsBody.querySelector('[data-empty-expenses]')?.remove();
                rowsBody.appendChild(row);
                recalculate();
            };

            document.addEventListener('input', event => {
                if (event.target.closest('[data-expense-row]')) {
                    recalculate();
                }
            });
            document.addEventListener('change', event => {
                if (event.target.closest('[data-expense-row]')) {
                    recalculate();
                }
            });
            addCustomButton?.addEventListener('click', addCustomExpense);
            recalculate();
        })();
    </script>
@endpush

@push('scripts')
    <script>
        (() => {
            const button = document.querySelector('[data-regenerate-description]');
            const dialog = document.querySelector('[data-description-dialog]');
            const textarea = document.getElementById('descripcion');
            const status = document.querySelector('[data-description-status]');

            const regenerate = async () => {
                dialog.close();
                button.disabled = true;
                status.textContent = 'Consultando las cartas relacionadas…';
                try {
                    const response = await fetch(button.dataset.url, {
                        headers: { Accept: 'application/json' },
                        cache: 'no-store',
                    });
                    if (!response.ok) throw new Error('No se pudo regenerar');
                    const data = await response.json();
                    if (typeof data.descripcion !== 'string') throw new Error('Respuesta inválida');
                    textarea.value = data.descripcion;
                    status.textContent = 'Descripción regenerada. Guarde la nota para conservarla.';
                } catch (error) {
                    status.textContent = 'No se pudo consultar la descripción. Intente nuevamente; su texto se conserva.';
                } finally {
                    button.disabled = false;
                }
            };

            button?.addEventListener('click', () => dialog.showModal());
            document.querySelector('[data-description-cancel]')?.addEventListener('click', () => dialog.close());
            document.querySelector('[data-description-confirm]')?.addEventListener('click', regenerate);
        })();
    </script>
@endpush
