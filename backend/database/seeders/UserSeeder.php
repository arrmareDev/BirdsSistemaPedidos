<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // ── Sistema — dueño de la plataforma ─────────
            [
                'name'     => 'Sistema',
                'email'    => 'sistema@mahoma.pe',
                'password' => Hash::make('sistema1234'),
                'role'     => 'sistema',
            ],

            // ── Admin — administrador del negocio ─────────
            [
                'name'     => 'Administrador',
                'email'    => 'admin@mahoma.pe',
                'password' => Hash::make('admin1234'),
                'role'     => 'admin',
            ],

            // ── Cajero — operador de caja ─────────────────
            [
                'name'     => 'Cajero',
                'email'    => 'cajero@mahoma.pe',
                'password' => Hash::make('cajero1234'),
                'role'     => 'cajero',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
