<?php

namespace App\Console\Commands;

use App\Models\Medico;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LinkVetUsers extends Command
{
    protected $signature = 'vets:link-users {--create-missing : Crea usuarios para mFdicos sin coincidencia}';
    protected $description = 'Vincula usuarios con rol vet a registros de medico_veterinario (user_id)';

    public function handle(): int
    {
        $createMissing = (bool) $this->option('create-missing');

        $linked = 0; $skipped = 0; $created = 0; $conflicts = 0;

        $vets = Medico::query()->get();
        if ($vets->isEmpty()) {
            $this->info('No hay registros en medico_veterinario.');
            return self::SUCCESS;
        }

        $this->info('Procesando '. $vets->count() .' fichas de mFdicos...');

        foreach ($vets as $m) {
            if ($m->user_id) { $skipped++; continue; }

            $full = trim(($m->nombre_mv ?? '').' '.($m->apell_mv ?? ''));
            $normalized = Str::lower(preg_replace('/\s+/', ' ', trim($full)));

            $user = User::query()
                ->where('role', 'vet')
                ->whereRaw('LOWER(TRIM(REPLACE(name, "  ", " "))) = ?', [$normalized])
                ->first();

            if ($user) {
                // Evitar vincular si ya estF asociado a otro mFdico
                $already = Medico::where('user_id', $user->id)->where('id_mv', '!=', $m->id_mv)->exists();
                if ($already) { $conflicts++; $this->warn("Conflicto: user {$user->id} ya estF vinculado a otro mFdico"); continue; }

                $m->user_id = $user->id; $m->save(); $linked++;
                $this->line("Vinculado: Medico {$m->id_mv} <- User {$user->id} ({$user->name})");
                continue;
            }

            if ($createMissing) {
                $emailBase = Str::slug($full ?: ('vet-'.$m->id_mv), '.');
                $domain = config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : 'orangehearth.local';
                if (!$domain) $domain = 'orangehearth.local';
                $email = $this->uniqueEmail($emailBase.'@'.$domain);
                $passwordPlain = Str::random(12);
                $user = User::create([
                    'name' => $full ?: ('Veterinario '.$m->id_mv),
                    'email' => $email,
                    'password' => Hash::make($passwordPlain),
                    'role' => 'vet',
                ]);
                $m->user_id = $user->id; $m->save(); $created++;
                $this->line("Creado y vinculado: Medico {$m->id_mv} <- User {$user->id} {$email} (pass: {$passwordPlain})");
                continue;
            }

            $this->warn("Sin coincidencia: Medico {$m->id_mv} {$full}");
        }

        $this->newLine();
        $this->info("Vinculados: {$linked}, Creados: {$created}, Conflictos: {$conflicts}, Omitidos: {$skipped}");
        return self::SUCCESS;
    }

    private function uniqueEmail(string $email): string
    {
        $try = $email; $i = 1;
        while (User::where('email', $try)->exists()) {
            $pos = strpos($email, '@');
            $local = substr($email, 0, $pos); $domain = substr($email, $pos);
            $try = $local."+{$i}".$domain; $i++;
        }
        return $try;
    }
}

