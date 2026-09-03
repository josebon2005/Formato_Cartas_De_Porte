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
    <label for="descripcion">Descripcion de la operacion</label>
    <textarea id="descripcion" name="descripcion">{{ old('descripcion', $descripcion ?? $notaGasto->descripcion ?? '') }}</textarea>
    @error('descripcion') <div class="error">{{ $message }}</div> @enderror
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
        <tbody>
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
                <tr>
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
            recalculate();
        })();
    </script>
@endpush
