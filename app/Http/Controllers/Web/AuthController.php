<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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

