<div class="actions table-actions pagos-actions">
    @if (! ($ocultarVer ?? false))
        <a class="btn secondary small" href="{{ route('pagos-pilotos.show', $pagoPiloto) }}">Ver</a>
    @endif
    @if ($pagoPiloto->estado === 'BORRADOR')
        <a class="btn secondary small" href="{{ route('pagos-pilotos.edit', $pagoPiloto) }}">Editar</a>
    @endif
    <a class="btn small" href="{{ route('pagos-pilotos.imprimir', $pagoPiloto) }}" target="_blank" rel="noopener">Imprimir</a>
    @if (($mostrarPagar ?? false) && $pagoPiloto->estado === 'BORRADOR')
        <form method="POST" action="{{ route('pagos-pilotos.pagar', $pagoPiloto) }}" onsubmit="return confirm('¿Marcar este pago como pagado? Quedará guardado en el historial y ya no podrá editarse.');">
            @csrf
            @method('PUT')
            <button class="btn success small" type="submit">Marcar como pagado</button>
        </form>
    @endif
    @if ($pagoPiloto->estado !== 'ANULADO')
        <form method="POST" action="{{ route('pagos-pilotos.anular', $pagoPiloto) }}" onsubmit="return confirm('¿Anular este pago? Se conservará en el historial y podrá generar otro pago para este período.');">
            @csrf
            @method('PUT')
            <button class="btn danger small" type="submit">Anular</button>
        </form>
    @endif
</div>
