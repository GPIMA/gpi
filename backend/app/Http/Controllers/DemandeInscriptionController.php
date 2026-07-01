<?php

namespace App\Http\Controllers;

use App\Enums\RoleUtilisateur;
use App\Mail\CompteApprouve;
use App\Models\DemandeInscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DemandeInscriptionController extends Controller
{
    /** Public — soumission depuis la vitrine. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
    'nom'         => ['required', 'string', 'max:120'],
    'prenom'      => ['required', 'string', 'max:120'],
    'email'       => ['required', 'email', 'unique:demandes_inscription,email', 'unique:users,email'],
    'role'        => ['required', 'in:ADMIN,TECHNICIEN,EMPLOYE'],
    'telephone'   => ['nullable', 'string', 'max:30'],
    'departement' => ['nullable', 'string', 'max:120'],
    'message'     => ['nullable', 'string', 'max:1000'],
]);

        DemandeInscription::create($data);

        return response()->json(['message' => 'Demande enregistrée.'], 201);
    }

    /** Admin — liste des demandes. */
    public function index(Request $request): JsonResponse
    {
        $demandes = DemandeInscription::query()
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->string('statut')))
            ->orderByRaw("CASE statut WHEN 'EN_ATTENTE' THEN 0 WHEN 'APPROUVEE' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($demandes);
    }

    /** Admin — approuver une demande : crée le compte et envoie le mail. */
    public function approuver(DemandeInscription $demande): JsonResponse
    {
        if ($demande->statut !== 'EN_ATTENTE') {
            return response()->json(['message' => 'Cette demande a déjà été traitée.'], 422);
        }

        // Générer un mot de passe lisible
        $motDePasse = 'Gpi@' . Str::upper(Str::random(4)) . rand(10, 99);

        User::create([
            'nom'      => $demande->nom,
            'prenom'   => $demande->prenom,
            'email'    => $demande->email,
            'role'     => RoleUtilisateur::from($demande->role),
            'password' => Hash::make($motDePasse),
        ]);

        $demande->update(['statut' => 'APPROUVEE']);

        // Envoyer les identifiants par e-mail
        Mail::to($demande->email)->send(new CompteApprouve($demande, $motDePasse));

        return response()->json(['message' => 'Compte créé et e-mail envoyé.']);
    }

    /** Admin — rejeter une demande. */
    public function rejeter(DemandeInscription $demande): JsonResponse
    {
        if ($demande->statut !== 'EN_ATTENTE') {
            return response()->json(['message' => 'Cette demande a déjà été traitée.'], 422);
        }

        $demande->update(['statut' => 'REJETEE']);

        return response()->json(['message' => 'Demande rejetée.']);
    }
}