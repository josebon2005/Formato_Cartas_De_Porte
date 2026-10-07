<tr data-viaje-row>
    <td>
        <input type="hidden" name="viajes[{{ $indice }}][id]" value="{{ $viaje['id'] ?? '' }}">
        <input type="hidden" name="viajes[{{ $indice }}][carta_porte_id]" value="{{ $viaje['carta_porte_id'] ?? '' }}">
        <input type="hidden" name="viajes[{{ $indice }}][es_manual]" value="{{ ! empty($viaje['es_manual']) ? '1' : '0' }}">
        <input type="hidden" name="viajes[{{ $indice }}][orden]" value="{{ $indice }}">
        <input type="date" name="viajes[{{ $indice }}][fecha]" value="{{ $viaje['fecha'] ?? '' }}" data-viaje-fecha aria-label="Fecha del viaje" required>
        <div class="field-help" data-viaje-fecha-legible>{{ preg_match('/^\d{4}-\d{2}-\d{2}$/', $viaje['fecha'] ?? '') ? implode('-', array_reverse(explode('-', $viaje['fecha']))) : 'dd-mm-aaaa' }}</div>
        <div class="field-help">{{ ! empty($viaje['es_manual']) ? 'Registro manual' : 'Carta de Porte' }}</div>
    </td>
    <td><input name="viajes[{{ $indice }}][referencia]" value="{{ $viaje['referencia'] ?? '' }}" maxlength="255" aria-label="Carta de Porte o referencia"></td>
    <td><input class="pagos-text" name="viajes[{{ $indice }}][consignatario]" value="{{ $viaje['consignatario'] ?? '' }}" maxlength="255" aria-label="Consignatario"></td>
    <td><input class="pagos-text" name="viajes[{{ $indice }}][destino]" value="{{ $viaje['destino'] ?? '' }}" maxlength="255" aria-label="Destino"></td>
    <td><input class="pagos-number" type="number" name="viajes[{{ $indice }}][valor]" value="{{ $viaje['valor'] ?? '0.00' }}" min="0" max="9999999999.99" step="0.01" inputmode="decimal" data-viaje-valor aria-label="Valor del viaje en quetzales" required></td>
    <td><input class="pagos-text" name="viajes[{{ $indice }}][observacion]" value="{{ $viaje['observacion'] ?? '' }}" maxlength="5000" aria-label="Observación del viaje (opcional)"></td>
    <td><button class="btn danger small" type="button" data-remove-row aria-label="Eliminar este viaje del pago">Eliminar</button></td>
</tr>
