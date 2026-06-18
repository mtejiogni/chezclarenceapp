<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Commande;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // =========================================================
    // LISTE DES UTILISATEURS
    // =========================================================

    public function index()
    {
        // Récupérer TOUS les utilisateurs non supprimés
        // (pas de pagination : le filtre est fait côté JS dans la vue)
        $users = User::whereNull('void')
            ->orderBy('role')
            ->orderBy('nom')
            ->get();

        // ── Compteurs pour l'en-tête ────────────────────────────
        // La vue utilise ces 4 variables nommément
        $totalUtilisateurs = $users->count();
        $totalActifs       = $users->where('statut', 'Activé')->count();
        $totalInactifs     = $users->where('statut', 'Désactivé')->count();
        $totalAdmins       = $users->where('role', 'Administrateur')->count();

        // ── Compteurs par rôle ──────
        $compteurs = User::whereNull('void')
            ->select('role', DB::raw('COUNT(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role');

        // ── Utilisateurs connectés en ce moment ─────────────────
        $connectes = User::where('etat', 'Connecté')
            ->whereNull('void')
            ->count();

        $roles = [
            'Administrateur',
            'Caissier',
            'Serveur',
            'Cuisinier',
            'Livreur',
            'Client',
        ];

        return view('utilisateur.index', compact(
            'users',
            'totalUtilisateurs',
            'totalActifs',
            'totalInactifs',
            'totalAdmins',
            'compteurs',
            'connectes',
            'roles'
        ));
    }

    // =========================================================
    // FORMULAIRE CRÉATION
    // =========================================================

    public function create()
    {
        $roles = [
            'Administrateur',
            'Caissier',
            'Serveur',
            'Cuisinier',
            'Livreur',
            'Client',
        ];

        return view('utilisateur.create', compact('roles'));
    }

    // =========================================================
    // ENREGISTRER UN NOUVEL UTILISATEUR
    // =========================================================

    public function store(RegisterRequest $request)
    {
        // Limite : maximum 3 comptes Administrateur
        if ($request->role === 'Administrateur') {
            $nbAdmins = User::where('role', 'Administrateur')
                ->whereNull('void')
                ->count();

            if ($nbAdmins >= 3) {
                return back()
                    ->withInput()
                    ->with('error', 'Limite atteinte : maximum 3 comptes Administrateur autorisés.');
            }
        }

        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Hasher le mot de passe
            $data['password'] = Hash::make($data['password']);

            // Upload photo de profil si fournie
            if ($request->hasFile('photo')) {
                $data['photo'] = $this->uploadPhoto($request->file('photo'));
            }

            // Valeurs par défaut
            $data['statut'] = 'Activé';         // statut par défaut à la création
            $data['etat']   = 'Déconnecté';
            $data['points'] = 0;

            $user = User::create($data);

            DB::commit();

            Log::info('Utilisateur créé', [
                'nouvel_user' => $user->email,
                'role'        => $user->role,
                'cree_par'    => Auth::user()->email,
            ]);

            return redirect()->route('admin.utilisateurs.index')
                ->with('success', "L'utilisateur « {$user->prenom} {$user->nom} » a été créé avec succès !");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création utilisateur', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    // =========================================================
    // DÉTAIL D'UN UTILISATEUR
    // =========================================================

    public function show(User $user)
    {
        if ($user->void) {
            abort(404);
        }

        $stats = [];

        if (in_array($user->role, ['Serveur', 'Caissier', 'Administrateur'])) {
            $stats['commandes_enregistrees'] = Commande::where('iduser', $user->iduser)
                ->whereNull('void')->count();

            $stats['ca_genere'] = Commande::where('iduser', $user->iduser)
                ->whereIn('statut_courant', ['Servie', 'Livrée'])
                ->whereNull('void')->sum('montant');

            $stats['commandes_du_mois'] = Commande::where('iduser', $user->iduser)
                ->whereNull('void')
                ->whereMonth('datecommande', now()->month)
                ->whereYear('datecommande', now()->year)
                ->count();
        }

        if ($user->role === 'Client') {
            $stats['commandes_passees'] = Commande::where('idclient', $user->iduser)
                ->whereNull('void')->count();

            $stats['montant_total'] = Commande::where('idclient', $user->iduser)
                ->whereIn('statut_courant', ['Servie', 'Livrée'])
                ->whereNull('void')->sum('montant');

            $stats['commandes_en_cours'] = Commande::where('idclient', $user->iduser)
                ->whereNotIn('statut_courant', ['Servie', 'Livrée', 'Annulée'])
                ->whereNull('void')->count();
        }

        $dernieresCommandes = Commande::where(function ($q) use ($user) {
                $q->where('iduser', $user->iduser)
                  ->orWhere('idclient', $user->iduser);
            })
            ->whereNull('void')
            ->with(['table', 'lignes'])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('utilisateur.show', compact(
            'user',
            'stats',
            'dernieresCommandes'
        ));
    }

    // =========================================================
    // FORMULAIRE MODIFICATION
    // =========================================================

    public function edit(User $user)
    {
        if ($user->void) {
            abort(404);
        }

        $peutModifierRole = $user->iduser !== Auth::id();

        $roles = [
            'Administrateur',
            'Caissier',
            'Serveur',
            'Cuisinier',
            'Livreur',
            'Client',
        ];

        return view('utilisateur.edit', compact('user', 'roles', 'peutModifierRole'));
    }

    // =========================================================
    // METTRE À JOUR UN UTILISATEUR
    // Statut : 'Activé' ou 'Désactivé'
    // =========================================================

    public function update(Request $request, User $user)
    {
        $userId = $user->iduser;

        $request->validate([
            'nom'       => 'required|string|max:128',
            'prenom'    => 'required|string|max:128',
            'sexe'      => 'nullable|in:Masculin,Féminin',
            'telephone' => 'required|string|max:20',
            'email'     => "required|email|max:128|unique:users,email,{$userId},iduser",
            'role'      => 'required|in:Administrateur,Caissier,Serveur,Cuisinier,Livreur,Client',
            'statut'    => 'required|in:Activé,Désactivé',   
            'adresse'   => 'nullable|string|max:500',
            'photo'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'password'  => 'nullable|string|min:8|confirmed|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
        ], [
            'nom.required'       => 'Le nom est obligatoire.',
            'prenom.required'    => 'Le prénom est obligatoire.',
            'telephone.required' => 'Le téléphone est obligatoire.',
            'email.required'     => "L'email est obligatoire.",
            'email.unique'       => 'Cet email est déjà utilisé par un autre compte.',
            'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'password.regex'     => 'Le mot de passe doit contenir une majuscule, un chiffre et un caractère spécial.',
            'photo.image'        => 'Le fichier doit être une image.',
            'photo.mimes'        => 'Formats acceptés : jpeg, png, jpg, webp.',
            'photo.max'          => 'La photo ne doit pas dépasser 2 Mo.',
        ]);

        // Empêcher un admin de se désactiver lui-même
        if ($user->iduser === Auth::id() && $request->statut === 'Désactivé') {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        // Empêcher un admin de modifier son propre rôle
        if ($user->iduser === Auth::id() && $request->role !== $user->role) {
            return back()->with('error', 'Vous ne pouvez pas modifier votre propre rôle.');
        }

        DB::beginTransaction();

        try {
            $data = $request->only([
                'nom', 'prenom', 'sexe', 'telephone',
                'email', 'role', 'statut', 'adresse',
            ]);

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            if ($request->hasFile('photo')) {
                $this->supprimerPhotoDisque($user->photo);
                $data['photo'] = $this->uploadPhoto($request->file('photo'));
            }

            // Si désactivé, déconnecter
            if ($request->statut === 'Désactivé' && $user->statut === 'Activé') {
                $data['etat'] = 'Déconnecté';
            }

            $user->update($data);

            DB::commit();

            Log::info('Utilisateur modifié', [
                'user'        => $user->email,
                'modifie_par' => Auth::user()->email,
            ]);

            return redirect()->route('admin.utilisateurs.index')
                ->with('success', "L'utilisateur « {$user->prenom} {$user->nom} » a été modifié avec succès !");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur modification utilisateur', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la modification : ' . $e->getMessage());
        }
    }

    // =========================================================
    // SUPPRIMER UN UTILISATEUR (Soft Delete via void)
    // =========================================================

    public function destroy(User $user)
    {
        if ($user->iduser === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $commandesActives = Commande::where(function ($q) use ($user) {
                $q->where('iduser', $user->iduser)
                  ->orWhere('idclient', $user->iduser);
            })
            ->whereNotIn('statut_courant', ['Servie', 'Livrée', 'Annulée'])
            ->whereNull('void')
            ->count();

        if ($commandesActives > 0) {
            return back()->with('error',
                "Impossible de supprimer « {$user->prenom} {$user->nom} » : {$commandesActives} commande(s) active(s) lui sont associées."
            );
        }

        DB::beginTransaction();

        try {
            $user->update([
                'void'       => '1',
                'statut'     => 'Désactivé',       
                'etat'       => 'Déconnecté',
                'deleted_at' => now(),
            ]);

            DB::commit();

            Log::info('Utilisateur supprimé', [
                'user'         => $user->email,
                'supprime_par' => Auth::user()->email,
            ]);

            return redirect()->route('admin.utilisateurs.index')
                ->with('success', "L'utilisateur « {$user->prenom} {$user->nom} » a été supprimé.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression utilisateur', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    // =========================================================
    // ACTIVER / DÉSACTIVER EN UN CLIC
    // Toggle statut : 'Activé' ↔ 'Désactivé'
    // =========================================================

    public function toggleStatut(User $user)
    {
        if ($user->iduser === Auth::id()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas modifier votre propre statut.',
                ], 403);
            }
            return back()->with('error', 'Vous ne pouvez pas modifier votre propre statut.');
        }

        // Basculer entre 'Activé' et 'Désactivé'
        $nouveauStatut = $user->statut === 'Activé' ? 'Désactivé' : 'Activé';

        $data = ['statut' => $nouveauStatut];

        if ($nouveauStatut === 'Désactivé') {
            $data['etat'] = 'Déconnecté';
        }

        $user->update($data);

        Log::info('Statut utilisateur modifié', [
            'user'   => $user->email,
            'statut' => $nouveauStatut,
            'par'    => Auth::user()->email,
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'statut'  => $nouveauStatut,
                'message' => "{$user->prenom} {$user->nom} est maintenant {$nouveauStatut}.",
            ]);
        }

        return back()->with(
            'success',
            "« {$user->prenom} {$user->nom} » est maintenant {$nouveauStatut}."
        );
    }

    // =========================================================
    // RÉINITIALISER LE MOT DE PASSE (par l'Admin)
    // La vue soumet un formulaire POST simple (pas de champ
    // password dans la modal) → mot de passe par défaut fixé ici
    // =========================================================

    public function resetPassword(User $user)
    {
        // Mot de passe par défaut affiché dans la SweetAlert de la vue
        $motDePasseDefaut = 'password123';

        $user->update([
            'password' => Hash::make($motDePasseDefaut),
        ]);

        Log::info('Mot de passe réinitialisé par Admin', [
            'user'  => $user->email,
            'admin' => Auth::user()->email,
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Mot de passe réinitialisé à « password123 ».',
            ]);
        }

        return back()->with(
            'success',
            "Mot de passe de « {$user->prenom} {$user->nom} » réinitialisé à « {$motDePasseDefaut} »."
        );
    }

    // =========================================================
    // SUPPRESSION PHOTO DE PROFIL
    // =========================================================

    public function supprimerPhoto(User $user)
    {
        if (!$user->photo) {
            return back()->with('error', "Cet utilisateur n'a pas de photo de profil.");
        }

        $this->supprimerPhotoDisque($user->photo);
        $user->update(['photo' => null]);

        return back()->with('success', 'Photo de profil supprimée.');
    }

    // =========================================================
    // STATISTIQUES GLOBALES (AJAX)
    // =========================================================

    public function statistiques()
    {
        $data = [
            'total'     => User::whereNull('void')->count(),
            'connectes' => User::where('etat', 'Connecté')->whereNull('void')->count(),
            'par_role'  => User::whereNull('void')
                ->select('role', DB::raw('COUNT(*) as total'))
                ->groupBy('role')
                ->pluck('total', 'role'),
            'actifs'    => User::where('statut', 'Activé')->whereNull('void')->count(),
            'inactifs'  => User::where('statut', 'Désactivé')->whereNull('void')->count(),
            'timestamp' => now()->format('H:i:s'),
        ];

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    // =========================================================
    // MÉTHODES PRIVÉES
    // =========================================================

    private function uploadPhoto($file): string
    {
        $nomFichier = uniqid('user_') . '_' . time()
            . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('users/avatars', $nomFichier, 'public');
    }

    private function supprimerPhotoDisque(?string $chemin): void
    {
        if ($chemin && Storage::disk('public')->exists($chemin)) {
            Storage::disk('public')->delete($chemin);
        }
    }
}