<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Datos ficticios exclusivamente para desarrollo y pruebas locales.
        if (!app()->environment('local', 'testing')) {
            throw new RuntimeException('Las cuentas demo solo se permiten en local o testing.');
        }

        $ids = DB::transaction(function () {
            $cuentas = [
                ['Usuario', 'usuario', 'USUARIO', '0000000001', 'DEMO-001'],
                ['Veterinario', 'vet', 'VETERINARIO', '0000000002', 'DEMO-002'],
                ['Administrador', 'admin', 'ADMINISTRADOR', '0000000003', 'DEMO-003'],
                ['Superadministrador', 'super', 'SUPERADMINISTRADOR', '0000000004', 'DEMO-004'],
            ];
            $ids = [];
            foreach ($cuentas as [$nombre, $user, $rol, $contacto, $documento]) {
                $email = $user . '@demo.test';
                $cuenta = DB::table('usuario')->where('email', $email)->first();
                if ($cuenta) {
                    // No elevar permisos ni sobrescribir contraseñas o perfiles existentes.
                    if ($cuenta->rol !== $rol || $cuenta->user !== $user) {
                        throw new RuntimeException('Conflicto con la cuenta demo: ' . $email);
                    }
                    $ids[$rol] = $cuenta->codigo_usuario;
                    continue;
                }
                $ids[$rol] = DB::table('usuario')->insertGetId([
                    'nombres' => $nombre,
                    'apellidos' => 'Demo',
                    'email' => $email,
                    'numero_contacto' => $contacto,
                    'numero_documento' => $documento,
                    'user' => $user,
                    // Formato BCrypt compatible con BCryptPasswordEncoder de Spring Security.
                    'password' => password_hash('Demo1234', PASSWORD_BCRYPT, ['cost' => 10]),
                    'rol' => $rol,
                ], 'codigo_usuario');
            }

            $clinica = DB::table('veterinarias')->where('nit', 'DEMO-900001')->first();
            $codigoClinica = $clinica ? $clinica->codigo_veterinarias :
                DB::table('veterinarias')->insertGetId([
                    'nit' => 'DEMO-900001',
                    'nombre' => 'Veterinaria PetPlan Demo',
                    'contacto' => '0000000010',
                    'estado' => 'Aprobada',
                    'fecha_aprobacion' => now()->toDateString(),
                ], 'codigo_veterinarias');

            $vet = DB::table('veterinario')->where('codigo_usuario', $ids['VETERINARIO'])->first();
            if (!$vet) {
                $usuario = DB::table('usuario')->where('codigo_usuario', $ids['VETERINARIO'])->first();
                DB::table('veterinario')->insert([
                    'nombres_vet' => $usuario->nombres,
                    'apellidos_vet' => $usuario->apellidos,
                    'num_contacto_vet' => $usuario->numero_contacto,
                    'email_vet' => $usuario->email,
                    'num_licencia' => 'DEMO-LIC-001',
                    'codigo_usuario' => $ids['VETERINARIO'],
                    'codigo_veterinarias' => $codigoClinica,
                ]);
            } elseif ((int) $vet->codigo_veterinarias !== (int) $codigoClinica) {
                throw new RuntimeException('El veterinario demo ya pertenece a otra clínica.');
            }

            $ids['CLINICA'] = $codigoClinica;
            return $ids;
        });

        $this->command?->info('Cuentas demo preparadas. No se modifican cuentas existentes.');
        $this->command?->info('Para habilitar al administrador demo, ejecuta en PowerShell antes de iniciar Java:');
        $this->command?->line(sprintf('$env:PETPLAN_ADMIN_CLINICAS=\'%s:%s\'', $ids['ADMINISTRADOR'], $ids['CLINICA']));
    }
}
