@extends('layouts.app')
@section('title', 'Pagos de Pilotos')
@section('container_class', 'container-wide')

@section('content')
    @include('pagos_pilotos._styles')
    <div class="page-head">
        <div><h1>Pagos de Pilotos</h1><p class="subtle">Seleccione un piloto para preparar y revisar su pago mensual.</p></div>
        <div class="actions"><a class="btn secondary" href="{{ route('pagos-pilotos.historial') }}">Historial de pagos</a></div>
    </div>
    @include('pagos_pilotos._errors')
    <section class="panel">
        <form class="pagos-filter" method="GET" action="{{ route('pagos-pilotos.index') }}">
            <div><label for="mes">Mes</label><select id="mes" name="mes">@foreach ($meses as $numero => $nombre)<option value="{{ $numero }}" @selected((int) $mes === (int) $numero)>{{ $nombre }}</option>@endforeach</select></div>
            <div><label for="anio">Año</label><input id="anio" name="anio" type="number" min="1900" max="9999" value="{{ $anio }}" required></div>
            <div class="actions"><button class="btn" type="submit">Consultar período</button></div>
        </form>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Piloto</th><th>Cabezal</th><th>Licencia</th><th>Estado · {{ $meses[$mes] }} {{ $anio }}</th><th>Acciones</th></tr></thead>
                <tbody>
                    @forelse ($pilotos as $piloto)
                        @php($pagoMes = $piloto->pagos->firstWhere('estado', '!=', 'ANULADO'))
                        <tr>
                            <td>{{ $piloto->nombre }} @if (! $piloto->activo)<span class="subtle">(inactivo)</span>@endif</td>
                            <td>{{ $piloto->cabezalUsual?->placa ?: '—' }}</td>
                            <td>{{ $piloto->licencias->pluck('numero')->filter()->implode(', ') ?: '—' }}</td>
                            <td><span class="status-badge {{ $pagoMes?->estado === 'PAGADO' ? 'billed' : ($pagoMes ? 'generated' : '') }}">{{ $pagoMes?->estado === 'PAGADO' ? 'Pagado' : ($pagoMes ? 'En proceso' : 'Pendiente') }}</span></td>
                            <td><div class="actions table-actions">
                                @if ($pagoMes)
                                    <a class="btn {{ $pagoMes->estado === 'BORRADOR' ? 'accent' : 'secondary' }} small" href="{{ route($pagoMes->estado === 'BORRADOR' ? 'pagos-pilotos.edit' : 'pagos-pilotos.show', $pagoMes) }}">{{ $pagoMes->estado === 'BORRADOR' ? 'Continuar pago' : 'Ver pago existente' }}</a>
                                @else
                                    <a class="btn accent small" href="{{ route('pagos-pilotos.create', ['piloto_id' => $piloto->id, 'mes' => $mes, 'anio' => $anio]) }}">Realizar pago</a>
                                @endif
                                <a class="btn secondary small" href="{{ route('pagos-pilotos.conceptos', $piloto) }}">Conceptos predeterminados</a>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="5">No hay pilotos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $pilotos->withQueryString()->links() }}</div>
    </section>
@endsection
