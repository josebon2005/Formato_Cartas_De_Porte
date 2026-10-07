<tr data-movimiento-row>
    <td>
        <input type="hidden" name="movimientos[{{ $indice }}][orden]" value="{{ $indice }}">
        <input class="pagos-text" name="movimientos[{{ $indice }}][concepto]" value="{{ $movimiento['concepto'] ?? '' }}" maxlength="255" aria-label="Concepto del movimiento" required>
    </td>
    <td><select name="movimientos[{{ $indice }}][tipo]" data-movimiento-tipo aria-label="Tipo de movimiento"><option value="SUMA" @selected(($movimiento['tipo'] ?? 'SUMA') === 'SUMA')>SUMA</option><option value="DESCUENTO" @selected(($movimiento['tipo'] ?? '') === 'DESCUENTO')>DESCUENTO</option></select></td>
    <td><input class="pagos-number" name="movimientos[{{ $indice }}][valor]" type="number" value="{{ $movimiento['valor'] ?? '0.00' }}" min="0" max="9999999999.99" step="0.01" inputmode="decimal" data-movimiento-valor aria-label="Valor del movimiento en quetzales" required></td>
    <td>
        <input type="hidden" name="movimientos[{{ $indice }}][aplicar]" value="0">
        <label class="pagos-check"><input type="checkbox" name="movimientos[{{ $indice }}][aplicar]" value="1" data-movimiento-aplicar @checked(! empty($movimiento['aplicar']))> Aplicar</label>
    </td>
    <td><input class="pagos-text" name="movimientos[{{ $indice }}][observacion]" value="{{ $movimiento['observacion'] ?? '' }}" maxlength="5000" aria-label="Observación del movimiento (opcional)"></td>
    <td><button class="btn danger small" type="button" data-remove-row aria-label="Eliminar este concepto">Eliminar</button></td>
</tr>
