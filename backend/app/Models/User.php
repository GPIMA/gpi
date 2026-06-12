<?php

namespace App\Models;

use App\Enums\RoleUtilisateur;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Utilisateur — the system's account. The `role` attribute discriminates the
 * Administrateur / Technicien / Employe sub-types from the class diagram.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'role',
        'specialite',
        'departement',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => RoleUtilisateur::class,
        ];
    }

    // — Rôles —————————————————————————————————————————————————

    public function estAdministrateur(): bool
    {
        return $this->role === RoleUtilisateur::ADMIN;
    }

    public function estTechnicien(): bool
    {
        return $this->role === RoleUtilisateur::TECHNICIEN;
    }

    public function estEmploye(): bool
    {
        return $this->role === RoleUtilisateur::EMPLOYE;
    }

    public function nomComplet(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    // — Relations ————————————————————————————————————————————

    /** Incidents signalés par cet employé. */
    public function incidentsSignales(): HasMany
    {
        return $this->hasMany(Incident::class, 'employe_id');
    }

    /** Incidents pris en charge par ce technicien. */
    public function incidentsTraites(): HasMany
    {
        return $this->hasMany(Incident::class, 'technicien_id');
    }

    /** Affectations d'équipements (employé). */
    public function affectations(): HasMany
    {
        return $this->hasMany(Affectation::class, 'employe_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'destinataire_id');
    }
}
