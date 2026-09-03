@extends('layouts.app')

@section('title', 'Agregar ' . $config['singular'])

@section('content')
    <div class="page-head">
        <div>
            <h1>Agregar {{ $config['singular'] }}</h1>
            <p class="subtle">Este dato quedara disponible para futuras cartas de porte.</p>
        </div>
        <div class="actions">
            <a class="btn secondary" href="{{ route('catalogos.index') }}">Volver a datos</a>
        </div>
    </div>

    <section class="panel">
        <form method="POST" action="{{ route('catalogos.store', $catalogo) }}">
            @csrf

            <div class="grid">
                <div class="span-2">
                    <label for="{{ $config['main'] }}">{{ $config['main_label'] }}</label>
                    <input id="{{ $config['main'] }}" name="{{ $config['main'] }}" required value="{{ old($config['main']) }}">
                    @error($config['main']) <div class="error">{{ $message }}</div> @enderror
                </div>

                @if (($config['extra'] ?? null) === 'descripcion')
                    <div class="span-2">
                        <label for="descripcion">{{ $config['extra_label'] }}</label>
                        <input id="descripcion" name="descripcion" value="{{ old('descripcion') }}">
                        @error('descripcion') <div class="error">{{ $message }}</div> @enderror
                    </div>
                @endif

                @if (($config['extras'] ?? null) === 'piloto_detalles')
                    <div>
                        <label for="licencia_numero">Licencia</label>
                        <input id="licencia_numero" name="licencia_numero" value="{{ old('licencia_numero') }}">
                        @error('licencia_numero') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label for="cabezal_placa">Placa usual</label>
                        <input id="cabezal_placa" name="cabezal_placa" list="cabezales_list" value="{{ old('cabezal_placa') }}">
                        <datalist id="cabezales_list">
                            @foreach ($cabezales as $cabezal)
                                <option value="{{ $cabezal->placa }}"></option>
                            @endforeach
                        </datalist>
                        @error('cabezal_placa') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label for="activo">Estado</label>
                        <select id="activo" name="activo">
                            <option value="1" @selected(old('activo', '1') === '1')>Activo</option>
                            <option value="0" @selected(old('activo') === '0')>Inactivo</option>
                        </select>
                        @error('activo') <div class="error">{{ $message }}</div> @enderror
                    </div>
                @endif
            </div>

            <div class="form-actions">
                <a class="btn secondary" href="{{ route('catalogos.index') }}">Cancelar</a>
                <button class="btn accent" type="submit">Guardar dato</button>
            </div>
        </form>
    </section>
@endsection
