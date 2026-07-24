<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestUserSeeder extends Seeder
{

    /**
     * Run the database seeds.
     * Para volver a ejecutar, si fuera necesario: php artisan db:seed --class=TestUserSeeder
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $this->seedUser('superadmin', 'superadmin_test@email.com', 'Sinclave1!', ['superadmin']);
            $this->seedUser('editorial', 'editorial_test@email.com', 'Sinclave1!', ['editorial']);
            //$this->seedUser('composer', 'composer_test@email.com', 'Sinclave1!', ['composer', 'musician']);
            $this->seedUser('composer', 'composer_test@email.com', 'Sinclave1!', ['musician']);
            $this->seedUser('musician', 'musician_test@email.com', 'Sinclave1!', ['musician']);
            $this->seedUser('teacher', 'teacher_test@email.com', 'Sinclave1!', ['musician']);
            $this->seedUser('archivist', 'archivist_test@email.com', 'Sinclave1!', ['musician']);

            // Commiteamos cambios
            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Seed a user with specified data and roles.
     *
     * @param string $name
     * @param string $email
     * @param string $password
     * @param array $roles
     */
    private function seedUser(string $name, string $email, string $password, array $roles): void
    {
        $user = User::firstWhere('email', $email);

        if ($user === null) {
            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->password = bcrypt($password);
            $user->save();
        }

        foreach ($roles as $roleName) {
            $role = Role::firstWhere('name', $roleName);

            if ($role && !$user->hasRole($roleName)) {
                $this->command->info('User: ' . $user->email . ' - Rol: ' . $role->name);
                $user->attachRole($role);
            } else {
                if ($role) {
                    $this->command->info('Already => User: ' . $user->email . ' - Rol: ' . $role->name);
                } else {
                    $this->command->error('Error: ' . $roleName . ' rol not found.');
                }
            }
        }
    }
}
