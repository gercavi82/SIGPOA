<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CreateAdministrator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sigpoa:create-administrator {email : Correo institucional del administrador}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea o habilita el administrador inicial sin exponer su contraseña';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $name = $this->ask('Nombre completo');
        $password = $this->secret('Contraseña');

        $validated = validator(
            ['name' => $name, 'email' => $email, 'password' => $password],
            ['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255'], 'password' => ['required', Password::min(12)->letters()->mixedCase()->numbers()->symbols()]],
        )->validate();

        $administratorRole = Role::query()->where('code', 'administrador')->where('is_active', true)->first();
        if (! $administratorRole) {
            $this->error('Ejecute primero php artisan db:seed --class=RbacSeeder.');

            return self::FAILURE;
        }

        $user = User::query()->updateOrCreate(
            ['email' => $validated['email']],
            ['name' => $validated['name'], 'password' => Hash::make($validated['password']), 'is_active' => true, 'password_changed_at' => now()],
        );
        $user->roles()->syncWithoutDetaching([$administratorRole->id]);

        $this->info('Administrador creado o actualizado correctamente.');

        return self::SUCCESS;
    }
}
