<?php

namespace App\Http\Controllers;

use App\Enums\RoleUtilisateur;
use App\Http\Requests\StoreUtilisateurRequest;
use App\Mail\CompteApprouve;
use App\Models\DemandeInscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DemandeInscriptionController extends Controller
{
    /** Public — soumission depuis la vitrine. */
    public function store(Request $request): JsonResponse
    {
        // Mêmes règles que la création d'un utilisateur par un admin
        // (StoreUtilisateurRequest) : nom/prénom sans chiffres, téléphone à
        // exactement 10 chiffres, département obligatoire (parmi la même
        // liste) pour un employé.
        $data = $request->validate([
    'nom'          => ['required', 'string', 'max:120', 'regex:/^[^0-9]+$/'],
    'prenom'       => ['required', 'string', 'max:120', 'regex:/^[^0-9]+$/'],
    'email'        => ['required', 'email', 'unique:demandes_inscription,email', 'unique:users,email'],
    'role'         => ['required', 'in:ADMIN,TECHNICIEN,EMPLOYE'],
    'telephone'    => ['required', 'string', 'regex:/^\d{10}$/'],
    'departement'  => ['required_if:role,EMPLOYE', 'nullable', 'string', Rule::in(StoreUtilisateurRequest::DEPARTEMENTS)],
    'message'      => ['nullable', 'string', 'max:1000'],
    'localisation' => ['nullable', 'in:Casablanca,Rabat,Tanger'],
], [
    'nom.regex' => 'Le nom ne doit pas contenir de chiffres.',
    'prenom.regex' => 'Le prénom ne doit pas contenir de chiffres.',
    'telephone.required' => 'Le numéro de téléphone est obligatoire.',
    'telephone.regex' => 'Le numéro de téléphone doit contenir exactement 10 chiffres.',
    'departement.required_if' => 'Le département est obligatoire pour un employé.',
]);

        DemandeInscription::create($data);

        return response()->json(['message' => 'Demande enregistrée.'], 201);
    }

    /**
     * Admin — liste des demandes. Super Admin : toutes. Admin lié à un site :
     * uniquement celles de son site (les demandes sans localisation restent
     * visibles par tout admin, faute de site à leur attribuer).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $demandes = DemandeInscription::query()
            ->when($user->role === RoleUtilisateur::ADMIN && $user->localisation, function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->whereNull('localisation')->orWhere('localisation', $user->localisation);
                });
            })
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->string('statut')))
            ->orderByRaw("CASE statut WHEN 'EN_ATTENTE' THEN 0 WHEN 'APPROUVEE' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($demandes);
    }

    /** Admin — approuver une demande : crée le compte et envoie le mail. */
    public function approuver(Request $request, DemandeInscription $demande): JsonResponse
    {
        $this->autoriserTraitement($request, $demande);

        if ($demande->statut !== 'EN_ATTENTE') {
            return response()->json(['message' => 'Cette demande a déjà été traitée.'], 422);
        }

        // Générer un mot de passe lisible
        $motDePasse = 'Gpi@' . Str::upper(Str::random(4)) . rand(10, 99);

        User::create([
            'nom'          => $demande->nom,
            'prenom'       => $demande->prenom,
            'email'        => $demande->email,
            'role'         => RoleUtilisateur::from($demande->role),
            'telephone'    => $demande->telephone,
            'departement'  => $demande->departement,
            'localisation' => $demande->localisation,
            'password'     => Hash::make($motDePasse),
        ]);

        $demande->update(['statut' => 'APPROUVEE']);

        // Envoyer les identifiants par e-mail
        Mail::to($demande->email)->send(new CompteApprouve($demande, $motDePasse));

        return response()->json(['message' => 'Compte créé et e-mail envoyé.']);
    }

    /** Admin — rejeter une demande. */
    public function rejeter(Request $request, DemandeInscription $demande): JsonResponse
    {
        $this->autoriserTraitement($request, $demande);

        if ($demande->statut !== 'EN_ATTENTE') {
            return response()->json(['message' => 'Cette demande a déjà été traitée.'], 422);
        }

        $demande->update(['statut' => 'REJETEE']);

        return response()->json(['message' => 'Demande rejetée.']);
    }

    /**
     * Un Admin lié à un site ne peut traiter que les demandes de son site
     * (ou celles sans localisation renseignée). Super Admin : sans restriction.
     */
    private function autoriserTraitement(Request $request, DemandeInscription $demande): void
    {
        $user = $request->user();

        if (
            $user->role === RoleUtilisateur::ADMIN
            && $user->localisation
            && $demande->localisation
            && $demande->localisation !== $user->localisation
        ) {
            throw new AccessDeniedHttpException(__('messages.forbidden'));
        }
    }
}