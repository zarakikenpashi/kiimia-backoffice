<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller{
    /**
     * Afficher tous les administrateurs
     */
    public function index(){
        $users = User::orderBy('id', 'desc')->get();
        
        return response()->json($users);
    }

    /**
     * Stocker un nouvel administrateur
     */
    public function store(Request $request){
        $validated = $request->validate([
            'name'     => 'required|string|min:3',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'photo'    => 'nullable|image|max:2048', // 2MB
            'fonction' => 'nullable|string|max:191',
        ]);

        // Upload photo
        $photoPath = $request->file('photo')->store('admins', 'public');

        $admin = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => "admin",
            'fonction'     => $validated['fonction'],
            'photo'    => $photoPath,
        ]);

        return response()->json([
            'message' => 'Administrateur créé avec succès',
            'admin' => $admin,
        ], 201);
    }


    /**
     * Afficher un administrateur
     */
    public function show($id){
        $admin = User::findOrFail($id);
        return response()->json($admin);
    }


    /**
     * Modifier un administrateur
     */
    public function update(Request $request, $id){
        // Récupérer l'administrateur
        $admin = User::findOrFail($id);

        // // Validation des champs
        $validated = $request->validate([
            'name'     => 'required|string|min:3',
            'email'    => 'required|email|unique:users,email,' . $admin->id,
            'password' => 'nullable|string|min:8',
            'fonction' => 'nullable|string|max:191',
            'photo'    => 'nullable|image|max:2048',
        ]);

        // Gestion de la photo si fournie
        if ($request->hasFile('photo')) {
            // Supprimer l’ancienne image si elle existe
            if ($admin->photo && Storage::disk('public')->exists($admin->photo)) {
                Storage::disk('public')->delete($admin->photo);
            }

            // Stocker la nouvelle image et mettre à jour le chemin
            $validated['photo'] = $request->file('photo')->store('admins', 'public');
        }

        // Hasher le mot de passe uniquement s'il est fourni
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            // Supprimer le mot de passe de la mise à jour pour ne pas écraser l'existant
            unset($validated['password']);
        }

        // Mettre à jour l'administrateur
        $admin->update($validated);

        return response()->json([
            'message' => 'Administrateur mis à jour avec succès',
            'admin' => $admin
        ]);
    }





    /**
     * Supprimer un administrateur
     */
    public function destroy($id){
        $admin = User::findOrFail($id);

        // Supprimer la photo
        if ($admin->photo && Storage::disk('public')->exists($admin->photo)) {
            Storage::disk('public')->delete($admin->photo);
        }

        $admin->delete();

        return response()->json([
            'message' => 'Administrateur supprimé'
        ]);
    }
}