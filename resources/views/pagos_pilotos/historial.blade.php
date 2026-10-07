@extends('layouts.app')
@section('title', 'Historial de pagos de pilotos')
@section('container_class', 'container-wide')

@section('content')
    @include('pagos_pilotos._styles')
    <div class="page-head"><div><h1>Historial de pagos</h1><p class="subtle">Consulte los borradores y los pagos guardados o anulados.</p></div><a class="btn secondary" href="{{ route('pagos-pilotos.index') }}">Pagos de Pilotos</a></div>
    @include('pagos_pilotos._errors')
    <section class="panel">
        <form class="pagos-filter" method="GET" action="{{ route('pagos-pilotos.historial') }}">
            <div><label for="piloto_id">Piloto</label><select id="piloto_id" name="piloto_id"><option value="">Todos</option>@foreach ($pilotos as $piloto)<option value="{{ $piloto->id }}" @selected((string) request('piloto_id') === (string) $piloto->id)>{{ $piloto->nombre }}</option>@endforeach</select></div>
            <div><label for="mes">Mes</label><select id="mes" name="mes"><option value="">Todos</option>@foreach ($meses as $numero => $nombre)<option value="{{ $numero }}" @selected((string) request('mes') === (string) $numero)>{{ $nombre }}</option>@endforeach</select></div>
            <div><label for="anio">Año</label><input id="anio" name="anio" type="number" min="1900" max="9999" value="{{ request('anio') }}" placeholder="Todos"></div>
            <div><label for="estado">Estado</label><select id="estado" name="estado"><option value="">Todos</option>@foreach ($estados as $valor => $nombre)<option value="{{ $valor }}" @selected(request('estado') === $valor)>{{ $nombre }}</option>@endforeach</select></div>
            <div class="actions"><button class="btn" type="submit">Buscar</button><a class="btn secondary" href="{{ route('pagos-pilotos.historial') }}">Limpiar</a></div>
        </form>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Piloto</th><th>Mes</th><th>Total viajes</th><th>Sueldo</th><th>Descuentos</th><th>Total a pagar / pagado</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                    @forelse ($pagos as $pagoPiloto)
                        <tr>
                            <td>{{ $pagoPiloto->piloto_nombre }}</td><td>{{ $meses[$pagoPiloto->mes] }} {{ $pagoPiloto->anio }}</td>
                            <td class="pagos-number">Q. {{ number_format((float) $pagoPiloto->total_viajes, 2) }}</td>
                            <td class="pagos-number">Q. {{ number_format((float) $pagoPiloto->sueldo_base, 2) }}</td>
                            <td class="pagos-number">Q. {{ number_format((float) $pagoPiloto->total_descuentos, 2) }}</td>
                            <td class="pagos-number"><strong>Q. {{ number_format((float) $pagoPiloto->total_pagar, 2) }}</strong></td>
                            <td><span class="status-badge {{ $pagoPiloto->estado === 'PAGADO' ? 'billed' : ($pagoPiloto->estado === 'ANULADO' ? 'cancelled' : 'generated') }}">{{ $pagoPiloto->estado }}</span></td>
                            <td>@include('pagos_pilotos._actions')</td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="8">No hay pagos para los filtros seleccionados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $pagos->withQueryString()->links() }}</div>
    </section>
@endsection
