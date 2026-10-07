<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\TrimStrings;

class PreservarDescripcionNota extends TrimStrings
{
    public function handle($request, Closure $next)
    {
        $except = $this->except;

        if ($request->is('facturacion/notas-gastos/*')) {
            $this->except[] = 'descripcion';
        }

        try {
            return parent::handle($request, $next);
        } finally {
            $this->except = $except;
        }
    }
}
