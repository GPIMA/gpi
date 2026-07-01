<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
    'name'    => 'required|string|max:255',
    'email'   => 'required|email|unique:users,email',
    'message' => 'nullable|string',
]);

        $user = User::create([
    'nom'      => $request->name,
    'prenom'   => '',
    'email'    => $request->email,
    'password' => Hash::make('Gpi@2026'),
    'role'     => 'EMPLOYE',
]);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès.',
            'user'    => $user,
        ], 201);
    }
}