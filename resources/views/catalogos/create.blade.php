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
            </div>

            <div class="form-actions">
                <a class="btn secondary" href="{{ route('catalogos.index') }}">Cancelar</a>
                <button class="btn accent" type="submit">Guardar dato</button>
            </div>
        </form>
    </section>
@endsection
