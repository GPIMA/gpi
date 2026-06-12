<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUtilisateurRequest;
use App\Http\Requests\UpdateUtilisateurRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

/**
 * Gestion des utilisateurs (cas d'utilisation « Gérer les utilisateurs »,
 * réservé à l'administrateur).
 */
class UtilisateurController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $users = User::query()
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $terme = '%'.$request->string('q').'%';
                $q->where(fn ($sub) => $sub
                    ->where('nom', 'ilike', $terme)
                    ->orWhere('prenom', 'ilike', $terme)
                    ->orWhere('email', 'ilike', $terme));
            })
            ->orderBy('nom')
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return UserResource::collection($users);
    }

    public function store(StoreUtilisateurRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return (new UserResource($user))
            ->additional(['message' => __('messages.utilisateur.cree')])
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateUtilisateurRequest $request, User $utilisateur): JsonResponse
    {
        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $utilisateur->update($data);

        return (new UserResource($utilisateur->fresh()))
            ->additional(['message' => __('messages.utilisateur.modifie')])
            ->response();
    }

    public function destroy(Request $request, User $utilisateur): JsonResponse
    {
        // On ne se supprime pas soi-même.
        if ($utilisateur->id === $request->user()->id) {
            abort(422, __('messages.utilisateur.auto_suppression'));
        }

        $utilisateur->delete();

        return response()->json(['message' => __('messages.utilisateur.supprime')]);
    }
}
