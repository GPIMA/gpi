<?php

namespace Database\Factories;

use App\Enums\RoleUtilisateur;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /** Prénoms (transcription latine) pour les données de démonstration. */
    private const PRENOMS = [
        'Mohammed', 'Ahmed', 'Youssef', 'Omar', 'Hamza', 'Karim', 'Mehdi', 'Yassine',
        'Khalid', 'Rachid', 'Anas', 'Bilal', 'Ayoub', 'Said', 'Hassan', 'Othmane',
        'Zakaria', 'Amine', 'Ismail', 'Tarik', 'Abderrahim', 'Nabil',
        'Fatima', 'Khadija', 'Aicha', 'Salma', 'Imane', 'Sara', 'Meryem', 'Hajar',
        'Nada', 'Yasmine', 'Houda', 'Soukaina', 'Loubna', 'Asmae', 'Ghita', 'Hanae',
        'Nadia', 'Samira', 'Kaoutar', 'Wijdane',
    ];

    /** Noms de famille (transcription latine). */
    private const NOMS = [
        'Benabdesselam', 'El Amrani', 'Bennani', 'El Fassi', 'Alaoui', 'Cherkaoui',
        'Idrissi', 'Tazi', 'Berrada', 'El Khattabi', 'Sebti', 'El Mansouri', 'Naciri',
        'Lahlou', 'El Ghazali', 'Chraibi', 'Benjelloun', 'Ouazzani', 'Belghiti',
        'El Yacoubi', 'Saadi', 'Hakimi', 'Bouzidi', 'Sefrioui', 'Kabbaj', 'Filali',
    ];

    public function definition(): array
    {
        $prenom = fake()->randomElement(self::PRENOMS);
        $nom = fake()->randomElement(self::NOMS);
        $slug = Str::slug($prenom.'.'.$nom);

        return [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $slug.'.'.fake()->unique()->numberBetween(1, 9999).'@gpi.local',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'telephone' => fake()->phoneNumber(),
            'role' => RoleUtilisateur::EMPLOYE,
            'departement' => fake()->randomElement(['Comptabilité', 'RH', 'Production', 'Commercial', 'Direction']),
            'remember_token' => Str::random(10),
        ];
    }

    public function administrateur(): static
    {
        return $this->state(fn () => [
            'role' => RoleUtilisateur::ADMIN,
            'departement' => null,
        ]);
    }

    public function technicien(): static
    {
        return $this->state(fn () => [
            'role' => RoleUtilisateur::TECHNICIEN,
            'departement' => null,
            'specialite' => fake()->randomElement(['Réseau', 'Systèmes', 'Support', 'Sécurité']),
        ]);
    }

    public function employe(): static
    {
        return $this->state(fn () => ['role' => RoleUtilisateur::EMPLOYE]);
    }
}
