<?php

namespace Database\Seeders;

use App\Enums\RoleUtilisateur;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FixedDemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $this->createUser('parc.demo_users.employe', RoleUtilisateur::EMPLOYE);
        $this->createUser('parc.demo_users.technicien', RoleUtilisateur::TECHNICIEN);
    }

    private function createUser(string $configKey, RoleUtilisateur $role): void
    {
        $account = config($configKey);

        if (empty($account['email']) || empty($account['password'])) {
            return;
        }

        User::updateOrCreate(
            ['email' => $account['email']],
            [
                'nom' => $account['nom'],
                'prenom' => $account['prenom'],
                'password' => Hash::make($account['password']),
                'role' => $role,
                'departement' => $account['departement'] ?? null,
            ],
        );
    }
}
