# Pagos de Pilotos y descripción de facturación

## Activar los cambios en esta computadora

Desde la carpeta del proyecto:

```powershell
php artisan migrate
php artisan optimize:clear
```

La migración nueva `2026_10_06_010000_create_pagos_pilotos_tables` crea únicamente las tablas `pagos_pilotos`, `pago_piloto_viajes`, `pago_piloto_movimientos` y `piloto_conceptos_pago`. No modifica migraciones anteriores ni datos existentes. Para la descripción se reutiliza `notas_gastos.descripcion`.

No hay nuevas dependencias de Composer ni npm. El JavaScript del módulo se sirve desde `public/js/pagos-pilotos.js`; esta actualización no necesita `npm install` ni `npm run build`.

## Guardar y enviar con Git

```powershell
git status
git add .
git commit -m "Agregar pagos de pilotos y mejorar descripcion de facturacion"
git push
```

## Actualizar la computadora clonada

Después del push, desde la carpeta del proyecto en la otra computadora:

```powershell
git pull
php artisan migrate
php artisan optimize:clear
```

## Uso del módulo

1. Abra **Pagos de Pilotos**. El listado muestra todos los pilotos y el estado del período seleccionado: pendiente, en proceso o pagado.
2. Opcionalmente, entre a **Conceptos predeterminados** para configurar ingresos y descuentos del piloto. Cambiar esta plantilla no modifica pagos guardados.
3. Pulse **Realizar pago**, seleccione mes/año y cargue los viajes del período.
4. Revise los valores, el sueldo base y los movimientos. Use **Agregar viaje** para registrar viajes sin Carta de Porte y **Aplicar** para activar o desactivar conceptos.
5. Guarde el borrador, revise la impresión y, al realizar el pago, pulse **Marcar como pagado**.

Los viajes automáticos se consultan por piloto y fecha. También se consideran cartas históricas sin `piloto_id` cuyo `piloto_nombre` coincide con el nombre del catálogo. Las tarifas existentes son cobros al cliente, por lo que los valores de viaje empiezan en Q. 0.00 y deben revisarse manualmente. El sueldo base también es editable; no se presupone un importe ni una tasa de IGSS o de seguro.

Las copias de viajes y movimientos pertenecen exclusivamente al pago. Cambiar una carta, un catálogo o una plantilla no cambia el histórico guardado. El cálculo se realiza en centavos tanto en la pantalla como en el servidor, excluyendo movimientos desactivados.

Solo los pagos **BORRADOR** se pueden editar. Los pagos **PAGADO** y **ANULADO** quedan bloqueados. Anular conserva todos los detalles y permite generar un nuevo pago del mismo piloto y período. Un índice único en la base de datos impide duplicados activos, incluso si se envían dos formularios a la vez.

El historial permite filtrar por piloto, mes, año y estado. La impresión muestra únicamente movimientos activos y está preparada para varias páginas en papel carta. En el diálogo de impresión del navegador, desactive **Encabezados y pies de página** para ocultar la URL y la fecha que agrega el propio navegador.

El período predeterminado y la fecha visible de pago usan `America/Guatemala`, configurado en `config/pagos_pilotos.php`. La zona horaria global de los módulos existentes se conserva.

## Descripción de facturación

La descripción automática usa las Cartas de Porte de la operación, en orden de correlativo, e incorpora todos los números de contenedor disponibles sin duplicados ni marcadores vacíos. La cantidad conserva el criterio existente: número de cartas agrupadas por BL y póliza.

El campo **Descripción de facturación** permite escribir libremente. Se conserva el texto guardado, incluidas mayúsculas, espacios, saltos de línea y una descripción vaciada intencionalmente. Guardar otros cambios, agregar la factura SAT o imprimir no regenera ese texto.

**Regenerar descripción** pide confirmación, consulta nuevamente las cartas relacionadas y reemplaza el texto del formulario. El reemplazo se persiste al guardar la nota. Si una nota antigua tiene descripción `NULL`, se muestra una descripción automática como compatibilidad; las notas con descripción existente conservan su texto hasta que se edite o regenere.

## Verificación

Las pruebas usan SQLite en memoria, configurado en `phpunit.xml`:

```powershell
php artisan test
```

La verificación cubre los flujos existentes y los nuevos: cálculo del pago, viajes y plantillas independientes, validaciones, duplicados, anulación, transacciones, descripción manual, contenedores, SAT e impresión. La migración de la base de trabajo se deja para los comandos de activación indicados arriba.
