<div class="grid">
    <div class="span-2">
        <label for="nombre">Nombre del cobro</label>
        <input id="nombre" name="nombre" required value="{{ old('nombre', $concepto->nombre) }}">
        @error('nombre') <div class="error">{{ $message }}</div> @enderror
    </div>
</div>

<div class="form-actions">
    <a class="btn secondary" href="{{ route('facturacion.conceptos-gastos.index') }}">Cancelar</a>
    <button class="btn accent" type="submit">Guardar cobro</button>
</div>
