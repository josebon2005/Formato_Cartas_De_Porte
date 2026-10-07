<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use JsonException;

class PagoPilotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        self::normalizarColecciones($this, ['viajes', 'movimientos']);
    }

    public static function normalizarColecciones(Request $request, array $campos): void
    {
        foreach ($campos as $campo) {
            if ($request->has($campo.'_json')) {
                // Un campo JSON evita el límite max_input_vars en meses con muchos viajes.
                try {
                    $json = $request->input($campo.'_json');
                    $filas = is_string($json) && str_starts_with(ltrim($json), '[')
                        ? json_decode($json, true, 64, JSON_THROW_ON_ERROR)
                        : null;
                } catch (JsonException) {
                    $filas = null;
                }
                if (! is_array($filas) || ! array_is_list($filas)) {
                    throw ValidationException::withMessages([$campo => 'No se pudo leer la lista de '.$campo.'. Revise el formulario y vuelva a guardar.']);
                }
                $request->merge([$campo => $filas]);
            } elseif ($request->input($campo) === null || $request->input($campo) === '') {
                $request->merge([$campo => []]);
            }
        }
    }

    public static function reglasDinero(): array
    {
        return ['required', 'numeric', 'min:0', 'max:9999999999.99', 'regex:/^\d{1,10}(\.\d{1,2})?$/D'];
    }

    public static function reglasMovimientos(): array
    {
        return [
            'movimientos' => ['present', 'array', 'max:500'],
            'movimientos.*' => ['array:concepto,tipo,valor,aplicar,observacion,orden'],
            'movimientos.*.concepto' => ['required', 'string', 'max:255'],
            'movimientos.*.tipo' => ['required', Rule::in(['SUMA', 'DESCUENTO'])],
            'movimientos.*.valor' => self::reglasDinero(),
            'movimientos.*.aplicar' => ['required', 'boolean'],
            'movimientos.*.observacion' => ['nullable', 'string', 'max:5000'],
            'movimientos.*.orden' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ];
    }

    public function rules(): array
    {
        $reglas = [
            'sueldo_base' => self::reglasDinero(),
            'observaciones' => ['nullable', 'string', 'max:10000'],
            'viajes' => ['present', 'array', 'max:500'],
            'viajes.*' => ['array:id,carta_porte_id,fecha,referencia,consignatario,destino,valor,es_manual,observacion,orden'],
            'viajes.*.id' => ['nullable', 'integer', 'min:1'],
            'viajes.*.carta_porte_id' => ['nullable', 'integer', 'min:1', 'distinct'],
            'viajes.*.fecha' => ['required', 'date_format:Y-m-d'],
            'viajes.*.referencia' => ['nullable', 'string', 'max:255'],
            'viajes.*.consignatario' => ['nullable', 'string', 'max:255'],
            'viajes.*.destino' => ['nullable', 'string', 'max:255'],
            'viajes.*.valor' => self::reglasDinero(),
            'viajes.*.es_manual' => ['required', 'boolean'],
            'viajes.*.observacion' => ['nullable', 'string', 'max:5000'],
            'viajes.*.orden' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ];

        if ($this->isMethod('post')) {
            $reglas += [
                'piloto_id' => ['required', 'integer', 'exists:pilotos,id'],
                'mes' => ['required', 'integer', 'between:1,12'],
                'anio' => ['required', 'integer', 'between:1900,9999'],
            ];
        }

        return $reglas + self::reglasMovimientos();
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'numeric' => 'El campo :attribute debe ser un importe válido.',
            'regex' => 'El importe debe ser positivo o cero, sin separadores de miles y con un máximo de dos decimales.',
            'min' => 'El campo :attribute debe ser al menos :min.',
            'max' => 'El campo :attribute supera el máximo permitido (:max).',
            'date_format' => 'Ingrese una fecha válida en :attribute.',
            'viajes.*.carta_porte_id.distinct' => 'Una Carta de Porte no puede repetirse dentro del pago.',
            'exists' => 'El registro seleccionado en :attribute ya no existe.',
            'in' => 'Seleccione una opción válida para :attribute.',
        ];
    }
}
