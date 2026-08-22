<?php

namespace App\Http\Controllers;

use App\Models\Equipement;
use App\Models\Historique;
use App\Models\User;
use Illuminate\Http\Request;

class HistoriqueController extends Controller
{
    /**
     * Historique d'un utilisateur : l'équipement concerné est renvoyé avec
     * tous ses détails (l'affichage ne montre que son nom dans le tableau,
     * le reste apparaît via le bouton "Détail").
     */
    public function parUtilisateur(User $utilisateur)
    {
        $historiques = Historique::where('user_id', $utilisateur->id)
            ->with(['equipement', 'auteur', 'incident.technicien'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($h) => [
                'id' => $h->id,
                'action' => $h->action,
                'description' => $h->description,
                'createdAt' => $h->created_at,
                'equipement' => $h->equipement ? [
                    'id' => $h->equipement->id,
                    'nom' => $h->equipement->nom,
                    'type' => $h->equipement->type?->value,
                    'typeLabel' => $h->equipement->type?->label(),
                    'marque' => $h->equipement->marque,
                    'modele' => $h->equipement->modele,
                    'numeroSerie' => $h->equipement->numero_serie,
                    'adresseIP' => $h->equipement->adresse_ip,
                    'adresseMAC' => $h->equipement->adresse_mac,
                    'etat' => $h->equipement->etat?->value,
                    'etatLabel' => $h->equipement->etat?->label(),
                    'localisation' => $h->equipement->localisation,
                    'dateAcquisition' => $h->equipement->date_acquisition,
                ] : null,
                'technicienAssigne' => $h->incident?->technicien?->nomComplet(),
                'auteur' => $h->auteur?->nomComplet(),
            ]);

        return response()->json($historiques);
    }

    /**
     * Historique des actions (+ commentaires) liés à un équipement :
     * l'utilisateur concerné est renvoyé avec tous ses détails (l'affichage
     * ne montre que son nom dans le tableau, le reste via "Détail").
     */
    public function parEquipement(Equipement $equipement)
    {
        $historiques = Historique::where('equipement_id', $equipement->id)
            ->with(['utilisateur', 'auteur', 'incident.technicien'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($h) => [
                'id' => $h->id,
                'action' => $h->action,
                'description' => $h->description,
                'createdAt' => $h->created_at,
                'utilisateur' => $h->utilisateur ? [
                    'id' => $h->utilisateur->id,
                    'nomComplet' => $h->utilisateur->nomComplet(),
                    'email' => $h->utilisateur->email,
                    'telephone' => $h->utilisateur->telephone,
                    'role' => $h->utilisateur->role?->value,
                    'roleLabel' => $h->utilisateur->role?->label(),
                    'departement' => $h->utilisateur->departement,
                    'localisation' => $h->utilisateur->localisation,
                ] : null,
                'technicienAssigne' => $h->incident?->technicien?->nomComplet(),
                'auteur' => $h->auteur?->nomComplet(),
            ]);

        return response()->json($historiques);
    }

    /** Ajout d'un commentaire libre sur un utilisateur. */
    public function commenterUtilisateur(Request $request, User $utilisateur)
    {
        $data = $request->validate([
            'commentaire' => ['required', 'string', 'max:1000'],
        ]);

        $auteur = $request->user();

        $historique = Historique::create([
            'user_id' => $utilisateur->id,
            'auteur_id' => $auteur->id,
            'action' => 'commentaire',
            'description' => $data['commentaire'],
        ]);

        return response()->json([
            'id' => $historique->id,
            'action' => $historique->action,
            'description' => $historique->description,
            'createdAt' => $historique->created_at,
            'equipement' => null,
            'technicienAssigne' => null,
            'auteur' => $auteur->nomComplet(),
        ], 201);
    }

    /** Ajout d'un commentaire libre sur un équipement. */
    public function commenter(Request $request, Equipement $equipement)
    {
        $data = $request->validate([
            'commentaire' => ['required', 'string', 'max:1000'],
        ]);

        $historique = Historique::create([
            'user_id' => $request->user()->id,
            'equipement_id' => $equipement->id,
            'action' => 'commentaire',
            'description' => $data['commentaire'],
        ]);

        $auteur = $request->user();

        return response()->json([
            'id' => $historique->id,
            'action' => $historique->action,
            'description' => $historique->description,
            'createdAt' => $historique->created_at,
            'utilisateur' => [
                'id' => $auteur->id,
                'nomComplet' => $auteur->nomComplet(),
                'email' => $auteur->email,
                'telephone' => $auteur->telephone,
                'role' => $auteur->role?->value,
                'roleLabel' => $auteur->role?->label(),
                'departement' => $auteur->departement,
                'localisation' => $auteur->localisation,
            ],
            'technicienAssigne' => null,
            'auteur' => null,
        ], 201);
    }
}