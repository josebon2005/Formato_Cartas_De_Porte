<div class="pagos-summary" aria-live="polite" aria-atomic="true">
    <dl>
        <div><dt>Total de viajes</dt><dd data-total="viajes">Q. {{ number_format((float) $pagoPiloto->total_viajes, 2) }}</dd></div>
        <div><dt>+ Sueldo base</dt><dd data-total="sueldo">Q. {{ number_format((float) $pagoPiloto->sueldo_base, 2) }}</dd></div>
        <div><dt>+ Bonificaciones y otros ingresos</dt><dd data-total="ingresos">Q. {{ number_format((float) $pagoPiloto->total_ingresos, 2) }}</dd></div>
        <div><dt>− Descuentos activos</dt><dd data-total="descuentos">Q. {{ number_format((float) $pagoPiloto->total_descuentos, 2) }}</dd></div>
        <div class="pagos-grand-total"><dt>TOTAL A PAGAR</dt><dd data-total="pagar">Q. {{ number_format((float) $pagoPiloto->total_pagar, 2) }}</dd></div>
    </dl>
</div>
