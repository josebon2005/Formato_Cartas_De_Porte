<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:create {email} {--name=Administrador}', function (string $email) {
    $password = $this->secret('Password del administrador');
    $confirmation = $this->secret('Confirmar password');

    if ($password !== $confirmation) {
        $this->error('Los passwords no coinciden.');

        return 1;
    }

    if (strlen((string) $password) < 8) {
        $this->error('El password debe tener al menos 8 caracteres.');

        return 1;
    }

    $user = User::updateOrCreate(
        ['email' => $email],
        [
            'name' => $this->option('name'),
            'password' => $password,
        ],
    );

    $this->info('Usuario administrador listo: '.$user->email);

    return 0;
})->purpose('Crear o actualizar el usuario administrador inicial');
