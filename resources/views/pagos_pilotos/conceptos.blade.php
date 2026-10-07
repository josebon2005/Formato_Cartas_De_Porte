@extends('layouts.app')
@section('title', 'Conceptos predeterminados de pago')
@section('container_class', 'container-wide')

@section('content')
    @include('pagos_pilotos._styles')
    @php($movimientosFormulario = session()->hasOldInput() ? (old('movimientos') ?: []) : $movimientos)
    <div class="page-head"><div><h1>Conceptos predeterminados</h1><p class="subtle">{{ $piloto->nombre }}</p></div><a class="btn secondary" href="{{ route('pagos-pilotos.index') }}">Volver a pilotos</a></div>
    @include('pagos_pilotos._errors')
    <form method="POST" action="{{ route('pagos-pilotos.guardar-conceptos', $piloto) }}" data-pago-form>
        @csrf
        @method('PUT')
        <section class="panel pagos-section">
            <div class="pagos-section-head"><h2>Plantilla del piloto</h2><button class="btn secondary" type="button" data-add-movimiento>+ Agregar concepto</button></div>
            <p class="subtle" style="margin-bottom: 14px;">Estos conceptos se cargarán al generar nuevos pagos. En cada mes podrá cambiar sus valores o desactivar «Aplicar». Los pagos ya guardados conservan su información.</p>
            <p class="field-help">El sueldo base se ingresa por separado en cada pago. Configure aquí bonificaciones, IGSS, seguro, adelantos y otros conceptos.</p>
            <input type="hidden" name="movimientos" value="">
            <div class="table-wrap">
                <table class="pagos-editor">
                    <thead><tr><th>Concepto</th><th>Tipo</th><th>Valor por defecto (Q.)</th><th>Aplicar por defecto</th><th>Observación</th><th>Acción</th></tr></thead>
                    <tbody data-movimientos>
                        @foreach ($movimientosFormulario as $indice => $movimiento)
                            @include('pagos_pilotos._movimiento_row')
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="empty" data-movimientos-empty @if (count($movimientosFormulario)) hidden @endif>Este piloto no tiene conceptos predeterminados.</p>
            <div class="form-actions actions"><a class="btn secondary" href="{{ route('pagos-pilotos.index') }}">Cancelar</a><button class="btn accent" type="submit">Guardar conceptos</button></div>
        </section>
    </form>
    <template id="pago-movimiento-template">@include('pagos_pilotos._movimiento_row', ['indice' => '__INDEX__', 'movimiento' => ['aplicar' => true]])</template>
@endsection

@section('scripts')
    <script src="{{ asset('js/pagos-pilotos.js') }}" defer></script>
@endsection
