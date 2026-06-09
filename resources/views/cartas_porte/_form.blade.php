@php
    $fechaValue = old('fecha', $cartaPorte->fecha?->format('Y-m-d') ?? now()->toDateString());
    $fechaVaporValue = old('fecha_vapor', $cartaPorte->fecha_vapor?->format('Y-m-d'));
@endphp

<div class="grid">
    <div>
        <label for="numero_correlativo">Numero correlativo</label>
        <input id="numero_correlativo" name="numero_correlativo" type="number" min="1" required value="{{ old('numero_correlativo', $cartaPorte->numero_correlativo) }}">
        @error('numero_correlativo') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="fecha">Fecha</label>
        <input id="fecha" name="fecha" type="date" required value="{{ $fechaValue }}">
        @error('fecha') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="destino">Destino</label>
        <input id="destino" name="destino" value="{{ old('destino', $cartaPorte->destino) }}">
        @error('destino') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="span-2">
        <label for="consignatario_nombre">Consignatario</label>
        <div class="catalog-input">
            <input id="consignatario_nombre" name="consignatario_nombre" list="consignatarios_list" required value="{{ old('consignatario_nombre', $cartaPorte->consignatario?->nombre) }}" autocomplete="off">
            <input id="consignatario_id" name="consignatario_id" type="hidden" value="{{ old('consignatario_id', $cartaPorte->consignatario_id) }}">
            <button class="btn secondary" type="button" onclick="markNew('consignatario_nombre', 'consignatario_id')">+ Nuevo</button>
        </div>
        <datalist id="consignatarios_list">
            @foreach ($consignatarios as $item)
                <option value="{{ $item->nombre }}"></option>
            @endforeach
        </datalist>
        @error('consignatario_nombre') <div class="error">{{ $message }}</div> @enderror
        @error('consignatario_id') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="procedencia_nombre">Procedencia</label>
        <div class="catalog-input">
            <input id="procedencia_nombre" name="procedencia_nombre" list="procedencias_list" required value="{{ old('procedencia_nombre', $cartaPorte->procedencia?->nombre) }}" autocomplete="off">
            <input id="procedencia_id" name="procedencia_id" type="hidden" value="{{ old('procedencia_id', $cartaPorte->procedencia_id) }}">
            <button class="btn secondary" type="button" onclick="markNew('procedencia_nombre', 'procedencia_id')">+ Nuevo</button>
        </div>
        <datalist id="procedencias_list">
            @foreach ($procedencias as $item)
                <option value="{{ $item->nombre }}"></option>
            @endforeach
        </datalist>
        @error('procedencia_nombre') <div class="error">{{ $message }}</div> @enderror
        @error('procedencia_id') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="poliza">Poliza</label>
        <input id="poliza" name="poliza" value="{{ old('poliza', $cartaPorte->poliza) }}">
        @error('poliza') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="id_documento">ID</label>
        <input id="id_documento" name="id_documento" value="{{ old('id_documento', $cartaPorte->id_documento) }}">
        @error('id_documento') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="da">DA</label>
        <input id="da" name="da" value="{{ old('da', $cartaPorte->da) }}">
        @error('da') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="mi">MI</label>
        <input id="mi" name="mi" value="{{ old('mi', $cartaPorte->mi) }}">
        @error('mi') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="contacto">Comunicarse con</label>
        <input id="contacto" name="contacto" value="{{ old('contacto', $cartaPorte->contacto) }}">
        @error('contacto') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="telefono">Telefono</label>
        <input id="telefono" name="telefono" value="{{ old('telefono', $cartaPorte->telefono) }}">
        @error('telefono') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="contenedor">Contenedor</label>
        <input id="contenedor" name="contenedor" value="{{ old('contenedor', $cartaPorte->contenedor) }}">
        @error('contenedor') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="bultos">Bultos</label>
        <input id="bultos" name="bultos" value="{{ old('bultos', $cartaPorte->bultos) }}">
        @error('bultos') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="peso_kls">Peso KLS</label>
        <input id="peso_kls" name="peso_kls" value="{{ old('peso_kls', $cartaPorte->peso_kls) }}">
        @error('peso_kls') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div class="span-3">
        <label for="contenido">Contenido</label>
        <textarea id="contenido" name="contenido">{{ old('contenido', $cartaPorte->contenido) }}</textarea>
        @error('contenido') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="vapor">Vapor</label>
        <input id="vapor" name="vapor" value="{{ old('vapor', $cartaPorte->vapor) }}">
        @error('vapor') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="fecha_vapor">Fecha vapor</label>
        <input id="fecha_vapor" name="fecha_vapor" type="date" value="{{ $fechaVaporValue }}">
        @error('fecha_vapor') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="bl">B/L</label>
        <input id="bl" name="bl" value="{{ old('bl', $cartaPorte->bl) }}">
        @error('bl') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="piloto_nombre">Piloto</label>
        <div class="catalog-input">
            <input id="piloto_nombre" name="piloto_nombre" list="pilotos_list" required value="{{ old('piloto_nombre', $cartaPorte->piloto?->nombre) }}" autocomplete="off">
            <input id="piloto_id" name="piloto_id" type="hidden" value="{{ old('piloto_id', $cartaPorte->piloto_id) }}">
            <button class="btn secondary" type="button" onclick="markNew('piloto_nombre', 'piloto_id')">+ Nuevo</button>
        </div>
        <datalist id="pilotos_list">
            @foreach ($pilotos as $item)
                <option value="{{ $item->nombre }}"></option>
            @endforeach
        </datalist>
        @error('piloto_nombre') <div class="error">{{ $message }}</div> @enderror
        @error('piloto_id') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="cabezal_placa">Cabezal placas</label>
        <div class="catalog-input">
            <input id="cabezal_placa" name="cabezal_placa" list="cabezales_list" required value="{{ old('cabezal_placa', $cartaPorte->cabezal?->placa) }}" autocomplete="off">
            <input id="cabezal_id" name="cabezal_id" type="hidden" value="{{ old('cabezal_id', $cartaPorte->cabezal_id) }}">
            <button class="btn secondary" type="button" onclick="markNew('cabezal_placa', 'cabezal_id')">+ Nuevo</button>
        </div>
        <datalist id="cabezales_list">
            @foreach ($cabezales as $item)
                <option value="{{ $item->placa }}"></option>
            @endforeach
        </datalist>
        @error('cabezal_placa') <div class="error">{{ $message }}</div> @enderror
        @error('cabezal_id') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="licencia_numero">Licencia</label>
        <div class="catalog-input">
            <input id="licencia_numero" name="licencia_numero" list="licencias_list" required value="{{ old('licencia_numero', $cartaPorte->licencia?->numero) }}" autocomplete="off">
            <input id="licencia_id" name="licencia_id" type="hidden" value="{{ old('licencia_id', $cartaPorte->licencia_id) }}">
            <button class="btn secondary" type="button" onclick="markNew('licencia_numero', 'licencia_id')">+ Nuevo</button>
        </div>
        <datalist id="licencias_list">
            @foreach ($licencias as $item)
                <option value="{{ $item->numero }}"></option>
            @endforeach
        </datalist>
        @error('licencia_numero') <div class="error">{{ $message }}</div> @enderror
        @error('licencia_id') <div class="error">{{ $message }}</div> @enderror
    </div>
</div>

<div class="form-actions">
    <a class="btn secondary" href="{{ route('cartas-porte.index') }}">Cancelar</a>
    <button class="btn accent" type="submit">{{ $cartaPorte->exists ? 'Actualizar carta' : 'Guardar e imprimir' }}</button>
</div>

@push('scripts')
    <script>
        const catalogos = {
            consignatario: @json($consignatarios->map(fn ($item) => ['id' => $item->id, 'value' => $item->nombre])->values()),
            procedencia: @json($procedencias->map(fn ($item) => ['id' => $item->id, 'value' => $item->nombre])->values()),
            piloto: @json($pilotos->map(fn ($item) => ['id' => $item->id, 'value' => $item->nombre])->values()),
            cabezal: @json($cabezales->map(fn ($item) => ['id' => $item->id, 'value' => $item->placa])->values()),
            licencia: @json($licencias->map(fn ($item) => ['id' => $item->id, 'value' => $item->numero])->values()),
        };

        function bindCatalog(inputId, hiddenId, items) {
            const input = document.getElementById(inputId);
            const hidden = document.getElementById(hiddenId);
            const sync = () => {
                const value = input.value.trim().toLowerCase();
                const match = items.find(item => item.value.toLowerCase() === value);
                hidden.value = match ? match.id : '';
            };

            input.addEventListener('input', sync);
            input.addEventListener('change', sync);
            sync();
        }

        function markNew(inputId, hiddenId) {
            document.getElementById(hiddenId).value = '';
            const input = document.getElementById(inputId);
            input.focus();
            input.select();
        }

        bindCatalog('consignatario_nombre', 'consignatario_id', catalogos.consignatario);
        bindCatalog('procedencia_nombre', 'procedencia_id', catalogos.procedencia);
        bindCatalog('piloto_nombre', 'piloto_id', catalogos.piloto);
        bindCatalog('cabezal_placa', 'cabezal_id', catalogos.cabezal);
        bindCatalog('licencia_numero', 'licencia_id', catalogos.licencia);
    </script>
@endpush
