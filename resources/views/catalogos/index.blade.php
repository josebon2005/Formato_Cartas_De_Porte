@extends('layouts.app')

@section('title', 'Datos')

@section('content')
    <div class="page-head">
        <div>
            <h1>Datos</h1>
            <p class="subtle">Administra consignatarios, procedencias, pilotos, placas y licencias reutilizables.</p>
        </div>
        <div class="actions">
            <a class="btn secondary" href="{{ route('cartas-porte.index') }}">Volver al listado</a>
        </div>
    </div>

    @foreach ($catalogos as $key => $catalogo)
        <section class="panel" style="margin-bottom: 18px;">
            <div class="page-head" style="margin-bottom: 12px;">
                <div>
                    <h1 style="font-size: 20px;">{{ $catalogo['titulo'] }}</h1>
                    <p class="subtle">{{ $catalogo['items']->count() }} registro(s)</p>
                </div>
                <div class="actions">
                    <a class="btn accent small" href="{{ route('catalogos.create', $key) }}">Agregar nuevo dato</a>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>{{ $catalogo['main_label'] }}</th>
                            @if (($catalogo['extras'] ?? null) === 'piloto_detalles')
                                <th>Licencia</th>
                                <th>Placa usual</th>
                            @endif
                            @if (isset($catalogo['extra_label']))
                                <th>{{ $catalogo['extra_label'] }}</th>
                            @endif
                            <th>Cartas usadas</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($catalogo['items'] as $item)
                            @php
                                $countKey = $catalogo['relation'].'_count';
                            @endphp
                            <tr>
                                <td>{{ $item->{$catalogo['main']} }}</td>
                                @if (($catalogo['extras'] ?? null) === 'piloto_detalles')
                                    <td>{{ $item->licencias->first()?->numero }}</td>
                                    <td>{{ $item->cabezalUsual?->placa }}</td>
                                @endif
                                @if (isset($catalogo['extra_label']))
                                    <td>{{ $item->descripcion }}</td>
                                @endif
                                <td>{{ $item->{$countKey} }}</td>
                                <td>
                                    <div class="actions">
                                        <a class="btn secondary small" href="{{ route('catalogos.edit', [$key, $item]) }}">Editar</a>
                                        <form method="POST" action="{{ route('catalogos.destroy', [$key, $item]) }}" onsubmit="return confirm('Este dato se eliminara de la lista para futuras cartas, pero las cartas ya creadas conservaran la informacion registrada. Desea continuar?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn danger small" type="submit">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 3 + (isset($catalogo['extra_label']) ? 1 : 0) + (($catalogo['extras'] ?? null) === 'piloto_detalles' ? 2 : 0) }}" class="empty">No hay registros en este apartado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endforeach
@endsection
