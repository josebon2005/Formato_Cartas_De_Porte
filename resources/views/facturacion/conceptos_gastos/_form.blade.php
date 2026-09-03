<div class="grid">
    <div class="span-2">
        <label for="nombre">Nombre</label>
        <input id="nombre" name="nombre" required value="{{ old('nombre', $concepto->nombre) }}">
        @error('nombre') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="codigo">Codigo</label>
        <input id="codigo" name="codigo" value="{{ old('codigo', $concepto->codigo) }}">
        @error('codigo') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="tipo_calculo">Tipo de calculo</label>
        <select id="tipo_calculo" name="tipo_calculo">
            <option value="fijo" @selected(old('tipo_calculo', $concepto->tipo_calculo) === 'fijo')>Fijo</option>
            <option value="por_contenedor" @selected(old('tipo_calculo', $concepto->tipo_calculo) === 'por_contenedor')>Por contenedor</option>
        </select>
        @error('tipo_calculo') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="grupo">Grupo</label>
        <select id="grupo" name="grupo">
            <option value="subtotal" @selected(old('grupo', $concepto->grupo) === 'subtotal')>Subtotal</option>
            <option value="adicional" @selected(old('grupo', $concepto->grupo) === 'adicional')>Adicional</option>
        </select>
        @error('grupo') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="orden">Orden</label>
        <input id="orden" name="orden" type="number" min="0" value="{{ old('orden', $concepto->orden ?? 0) }}">
        @error('orden') <div class="error">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="activo">Activo</label>
        <select id="activo" name="activo">
            <option value="1" @selected(old('activo', $concepto->activo ?? true))>Si</option>
            <option value="0" @selected(! old('activo', $concepto->activo ?? true))>No</option>
        </select>
        @error('activo') <div class="error">{{ $message }}</div> @enderror
    </div>
</div>

<div class="form-actions">
    <a class="btn secondary" href="{{ route('facturacion.conceptos-gastos.index') }}">Cancelar</a>
    <button class="btn accent" type="submit">Guardar concepto</button>
</div>
