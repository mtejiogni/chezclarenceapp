<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion.
     * Si l'utilisateur est DÉJÀ connecté, inutile de réafficher le formulaire :
     * on le renvoie directement vers son tableau de bord.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Affiche le formulaire d'inscription.
     * Réservé à la création de comptes CLIENT — le personnel
     * (Serveur, Cuisinier, Caissier, Livreur, Administrateur) est
     * créé exclusivement par un administrateur via UserController.
     * Si l'utilisateur est déjà connecté, inutile de lui montrer ce
     * formulaire : on le renvoie vers son tableau de bord.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    /**
     * Traite la soumission du formulaire d'inscription.
     *
     * [SÉCURITÉ] Le rôle 'Client' est codé en dur ci-dessous et
     * n'est JAMAIS lu depuis la requête : un visiteur ne peut donc
     * pas s'auto-attribuer un rôle privilégié (Administrateur,
     * Caissier, etc.) en trafiquant le formulaire, même si le champ
     * était ajouté côté client. La création des comptes du
     * personnel reste exclusivement réservée à
     * UserController::store() (protégé par le middleware
     * role:Administrateur).
     */
    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom'       => ['required', 'string', 'max:128'],
            'prenom'    => ['required', 'string', 'max:128'],
            'sexe'      => ['nullable', 'in:Masculin,Féminin'],
            'telephone' => ['nullable', 'string', 'max:128'],
            'email'     => ['required', 'email', 'max:128', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'nom.required'       => 'Le nom est obligatoire.',
            'prenom.required'    => 'Le prénom est obligatoire.',
            'email.required'     => 'L\'adresse email est obligatoire.',
            'email.email'        => 'Le format de l\'adresse email est invalide.',
            'email.unique'       => 'Un compte existe déjà avec cette adresse email.',
            'password.required'  => 'Le mot de passe est obligatoire.',
            'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
        ]);

        // ---- Création du compte (rôle Client forcé) ----
        $user = User::create([
            'nom'       => trim($data['nom']),
            'prenom'    => trim($data['prenom']),
            'sexe'      => $data['sexe'] ?? null,
            'telephone' => $data['telephone'] ?? null,
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => 'Client',
            'statut'    => 'Activé',
            'etat'      => 'Connecté',
            'points'    => 0,
        ]);

        Log::info('Nouveau compte client créé', [
            'email'  => $user->email,
            'iduser' => $user->iduser,
        ]);

        // ---- Connexion automatique + régénération de session ----
        // Même protection anti-fixation de session que login().
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Bienvenue, ' . $user->prenom . ' ! Votre compte a été créé avec succès.');
    }

    /**
     * Traite la soumission du formulaire de connexion.
     */
    public function login(Request $request): RedirectResponse
    {
        // ---- 1) Validation des champs reçus ----
        // Si la validation échoue, Laravel renvoie automatiquement l'utilisateur
        // au formulaire avec les messages d'erreur ci-dessous.
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ], [
            'email.required'    => 'L\'adresse email est obligatoire.',
            'email.email'       => 'Le format de l\'adresse email est invalide.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min'      => 'Le mot de passe doit contenir au moins 6 caractères.',
        ]);
 
        // Case à cocher "Se souvenir de moi" (cookie de longue durée).
        $remember = $request->boolean('remember');
 
        // ---- 2) Tentative d'authentification ----
        // Auth::attempt() hache le mot de passe fourni et le compare au hash stocké.
        // Il renvoie true si les identifiants sont corrects, false sinon.
        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
 
            // ---- 3) Le compte est-il activé ? ----
            if ($user->statut !== 'Activé') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Votre compte est désactivé.',
                ]);
            }
 
            // ---- 4) Mise à jour de l'état + journalisation ----
            $user->update(['etat' => 'Connecté']);
            Log::info('Connexion réussie', [
                'email' => $user->email,
                'role'  => $user->role,
            ]);
 
            // ---- 5) Régénérer l'identifiant de session ----
            // Protection contre la "fixation de session" : on change l'ID de
            // session après connexion pour empêcher un attaquant de réutiliser
            // un ancien identifiant.
            $request->session()->regenerate();
 
            // intended() renvoie vers la page initialement demandée, sinon le dashboard.
            return redirect()->intended(route('dashboard'));
        }
 
        // ---- 6) Échec de connexion ----
        // On journalise la tentative (utile pour détecter une attaque par force brute)
        // et on affiche un message GÉNÉRIQUE : ne jamais préciser si c'est l'email
        // ou le mot de passe qui est faux (cela aiderait un attaquant).
        Log::warning('Tentative de connexion échouée', [
            'email' => $request->input('email'),
            'ip'    => $request->ip(),
        ]);
 
        return back()
            ->withErrors(['email' => 'Email ou mot de passe incorrect.'])
            ->onlyInput('email'); // On re-remplit le champ email, jamais le mot de passe.
    }
 
    /**
     * Déconnecte l'utilisateur et détruit complètement la session.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            $user->update(['etat' => 'Déconnecté']);
        }
 
        Auth::logout();                      // Oublie l'utilisateur courant
        $request->session()->invalidate();   // Détruit toutes les données de session
        $request->session()->regenerateToken(); // Régénère le jeton CSRF
 
        return redirect()
            ->route('login')
            ->with('success', 'Vous avez été déconnecté avec succès.');
    }
}