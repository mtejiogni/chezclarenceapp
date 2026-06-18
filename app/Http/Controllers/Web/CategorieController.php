<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CategorieController extends Controller
{
    // =========================================================
    // LISTE DES CATÉGORIES
    // =========================================================

    public function index(Request $request)
    {
        $query = Categorie::withCount(['menus' => function ($q) {
                $q->whereNull('void');
            }])
            ->withCount(['menusActifs'])
            ->whereNull('void')
            ->orderBy('intitule');

        // Recherche par nom
        if ($request->filled('q')) {
            $query->where('intitule', 'like', '%' . $request->q . '%');
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $categories    = $query->paginate(10)->withQueryString();
        $totalActives  = Categorie::where('statut', 'Activé')->whereNull('void')->count();
        $totalInactives = Categorie::where('statut', 'Désactivé')->whereNull('void')->count();

        return view('categorie.index', compact(
            'categories',
            'totalActives',
            'totalInactives'
        ));
    }

    // =========================================================
    // FORMULAIRE CRÉATION
    // =========================================================

    public function create()
    {
        return view('categorie.create');
    }

    // =========================================================
    // ENREGISTRER UNE NOUVELLE CATÉGORIE
    // =========================================================

    public function store(Request $request)
    {
        $request->validate([
            'intitule'    => 'required|string|max:128|unique:categories,intitule',
            'description' => 'nullable|string|max:500',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'statut'      => 'required|in:Activé,Désactivé',
        ], [
            'intitule.required' => 'Le nom de la catégorie est obligatoire.',
            'intitule.max'      => 'Le nom ne peut pas dépasser 128 caractères.',
            'intitule.unique'   => 'Une catégorie avec ce nom existe déjà.',
            'photo.image'       => 'Le fichier doit être une image.',
            'photo.mimes'       => 'Formats acceptés : jpeg, png, jpg, webp.',
            'photo.max'         => 'La photo ne doit pas dépasser 2 Mo.',
            'statut.required'   => 'Le statut est obligatoire.',
            'statut.in'         => 'Statut invalide.',
        ]);

        DB::beginTransaction();

        try {
            $data = $request->only(['intitule', 'description', 'statut']);

            // Nettoyer et formater le nom
            $data['intitule'] = ucfirst(trim($data['intitule']));

            // Upload photo si fournie
            if ($request->hasFile('photo')) {
                $data['photo'] = $this->uploadPhoto($request->file('photo'));
            }

            $categorie = Categorie::create($data);

            DB::commit();

            Log::info('Catégorie créée', [
                'categorie' => $categorie->intitule,
                'user'      => Auth::user()->email,
            ]);

            return redirect()->route('admin.categories.index')
                ->with('success', "La catégorie « {$categorie->intitule} » a été créée avec succès !");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création catégorie', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    // =========================================================
    // DÉTAIL D'UNE CATÉGORIE
    // =========================================================

    public function show(Categorie $categorie)
    {
        if ($categorie->void) {
            abort(404);
        }

        // Plats de la catégorie avec statistiques
        $menus = Menu::where('idcategorie', $categorie->idcategorie)
            ->whereNull('void')
            ->orderBy('statut')
            ->orderBy('intitule')
            ->get();

        // Statistiques de ventes de la catégorie
        $statsVentes = DB::table('lignes')
            ->join('menus', 'lignes.idmenu', '=', 'menus.idmenu')
            ->join('commandes', 'lignes.idcommande', '=', 'commandes.idcommande')
            ->where('menus.idcategorie', $categorie->idcategorie)
            ->whereNull('commandes.void')
            ->select(
                DB::raw('SUM(lignes.quantite) as total_vendu'),
                DB::raw('SUM(lignes.prix) as ca_total'),
                DB::raw('COUNT(DISTINCT commandes.idcommande) as nb_commandes')
            )
            ->first();

        // Top 5 plats de la catégorie
        $topPlats = DB::table('lignes')
            ->join('menus', 'lignes.idmenu', '=', 'menus.idmenu')
            ->join('commandes', 'lignes.idcommande', '=', 'commandes.idcommande')
            ->where('menus.idcategorie', $categorie->idcategorie)
            ->whereNull('commandes.void')
            ->select(
                'menus.intitule',
                DB::raw('SUM(lignes.quantite) as total_vendu'),
                DB::raw('SUM(lignes.prix) as ca_plat')
            )
            ->groupBy('menus.idmenu', 'menus.intitule')
            ->orderByDesc('total_vendu')
            ->take(5)
            ->get();

        return view('categorie.show', compact(
            'categorie',
            'menus',
            'statsVentes',
            'topPlats'
        ));
    }

    // =========================================================
    // FORMULAIRE MODIFICATION
    // =========================================================

    public function edit(Categorie $categorie)
    {
        if ($categorie->void) {
            abort(404);
        }

        return view('categorie.edit', compact('categorie'));
    }

    // =========================================================
    // METTRE À JOUR UNE CATÉGORIE
    // =========================================================

    public function update(Request $request, Categorie $categorie)
    {
        $request->validate([
            'intitule'    => 'required|string|max:128|unique:categories,intitule,' . $categorie->idcategorie . ',idcategorie',
            'description' => 'nullable|string|max:500',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'statut'      => 'required|in:Activé,Désactivé',
        ], [
            'intitule.required' => 'Le nom de la catégorie est obligatoire.',
            'intitule.unique'   => 'Une autre catégorie porte déjà ce nom.',
            'photo.image'       => 'Le fichier doit être une image.',
            'photo.mimes'       => 'Formats acceptés : jpeg, png, jpg, webp.',
            'photo.max'         => 'La photo ne doit pas dépasser 2 Mo.',
        ]);

        DB::beginTransaction();

        try {
            $data = $request->only(['intitule', 'description', 'statut']);
            $data['intitule'] = ucfirst(trim($data['intitule']));

            // Nouvelle photo : supprimer l'ancienne et uploader
            if ($request->hasFile('photo')) {
                $this->supprimerPhoto($categorie->photo);
                $data['photo'] = $this->uploadPhoto($request->file('photo'));
            }

            // Si la catégorie est désactivée, désactiver aussi tous ses plats
            if ($request->statut === 'Désactivé' && $categorie->statut === 'Activé') {
                Menu::where('idcategorie', $categorie->idcategorie)
                    ->whereNull('void')
                    ->update(['statut' => 'Désactivé']);

                Log::info('Plats désactivés automatiquement', [
                    'categorie' => $categorie->intitule,
                    'user'      => Auth::user()->email,
                ]);
            }

            $categorie->update($data);

            DB::commit();

            Log::info('Catégorie modifiée', [
                'categorie' => $categorie->intitule,
                'user'      => Auth::user()->email,
            ]);

            return redirect()->route('admin.categories.index')
                ->with('success', "La catégorie « {$categorie->intitule} » a été modifiée avec succès !");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur modification catégorie', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la modification : ' . $e->getMessage());
        }
    }

    // =========================================================
    // SUPPRIMER UNE CATÉGORIE (Soft Delete)
    // =========================================================

    public function destroy(Categorie $categorie)
    {
        // Vérifier s'il existe des plats actifs dans cette catégorie
        $platsActifs = Menu::where('idcategorie', $categorie->idcategorie)
            ->where('statut', 'Activé')
            ->whereNull('void')
            ->count();

        if ($platsActifs > 0) {
            return back()->with('error',
                "Impossible de supprimer « {$categorie->intitule} » : elle contient {$platsActifs} plat(s) actif(s). Désactivez-les d'abord."
            );
        }

        // Vérifier si des plats de cette catégorie sont dans des commandes actives
        $commandesActives = DB::table('lignes')
            ->join('commandes', 'lignes.idcommande', '=', 'commandes.idcommande')
            ->join('menus', 'lignes.idmenu', '=', 'menus.idmenu')
            ->where('menus.idcategorie', $categorie->idcategorie)
            ->whereNotIn('commandes.statut_courant', ['Servie', 'Livrée', 'Annulée'])
            ->whereNull('commandes.void')
            ->count();

        if ($commandesActives > 0) {
            return back()->with('error',
                "Impossible de supprimer « {$categorie->intitule} » : des plats sont dans {$commandesActives} commande(s) active(s)."
            );
        }

        DB::beginTransaction();

        try {
            // Soft delete de tous les plats de la catégorie
            Menu::where('idcategorie', $categorie->idcategorie)
                ->whereNull('void')
                ->update([
                    'void'       => '1',
                    'statut'     => 'Désactivé',
                    'deleted_at' => now(),
                ]);

            // Soft delete de la catégorie
            $categorie->update([
                'void'       => '1',
                'statut'     => 'Désactivé',
                'deleted_at' => now(),
            ]);

            // Supprimer la photo si elle existe
            $this->supprimerPhoto($categorie->photo);

            DB::commit();

            Log::info('Catégorie supprimée', [
                'categorie' => $categorie->intitule,
                'user'      => Auth::user()->email,
            ]);

            return redirect()->route('admin.categories.index')
                ->with('success', "La catégorie « {$categorie->intitule} » et ses plats ont été supprimés.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression catégorie', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    // =========================================================
    // ACTIVER / DÉSACTIVER EN UN CLIC
    // =========================================================

    public function toggleStatut(Categorie $categorie)
    {
        $nouveauStatut = $categorie->statut === 'Activé' ? 'Désactivé' : 'Activé';

        DB::beginTransaction();

        try {
            $categorie->update(['statut' => $nouveauStatut]);

            // Si on désactive la catégorie, désactiver aussi tous ses plats
            if ($nouveauStatut === 'Désactivé') {
                $nbPlats = Menu::where('idcategorie', $categorie->idcategorie)
                    ->where('statut', 'Activé')
                    ->whereNull('void')
                    ->update(['statut' => 'Désactivé']);

                Log::info('Catégorie + plats désactivés', [
                    'categorie' => $categorie->intitule,
                    'nb_plats'  => $nbPlats,
                    'user'      => Auth::user()->email,
                ]);
            }

            DB::commit();

            Log::info('Statut catégorie modifié', [
                'categorie' => $categorie->intitule,
                'statut'    => $nouveauStatut,
                'user'      => Auth::user()->email,
            ]);

            // Réponse JSON si appel AJAX
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'statut'  => $nouveauStatut,
                    'message' => "« {$categorie->intitule} » est maintenant {$nouveauStatut}.",
                ]);
            }

            return back()->with(
                'success',
                "« {$categorie->intitule} » est maintenant {$nouveauStatut}."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // =========================================================
    // SUPPRESSION PHOTO UNIQUEMENT
    // =========================================================

    public function supprimerPhotoCat(Categorie $categorie)
    {
        if (!$categorie->photo) {
            return back()->with('error', 'Cette catégorie n\'a pas de photo.');
        }

        $this->supprimerPhoto($categorie->photo);
        $categorie->update(['photo' => null]);

        return back()->with('success', 'Photo supprimée avec succès.');
    }

    // =========================================================
    // LISTE POUR AJAX (sélecteur de catégorie)
    // =========================================================

    public function listeActive()
    {
        $categories = Categorie::with(['menusActifs'])
            ->where('statut', 'Activé')
            ->whereNull('void')
            ->orderBy('intitule')
            ->get()
            ->map(function ($cat) {
                return [
                    'idcategorie' => $cat->idcategorie,
                    'intitule'    => $cat->intitule,
                    'description' => $cat->description,
                    'photo_url'   => $cat->photo_url,
                    'nb_plats'    => $cat->menus_actifs_count ?? 0,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $categories,
        ]);
    }

    // =========================================================
    // MÉTHODES PRIVÉES
    // =========================================================

    /**
     * Uploader une photo de catégorie
     */
    private function uploadPhoto($file): string
    {
        $nomFichier = uniqid('cat_') . '_' . time() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('categories/photos', $nomFichier, 'public');
    }

    /**
     * Supprimer une photo du disque
     */
    private function supprimerPhoto(?string $chemin): void
    {
        if ($chemin && Storage::disk('public')->exists($chemin)) {
            Storage::disk('public')->delete($chemin);
        }
    }
}