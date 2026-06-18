<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Categorie;
use App\Http\Requests\MenuRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    // =========================================================
    // LISTE DES MENUS
    // =========================================================

    public function index(Request $request)
    {
        $query = Menu::with('categorie')
            ->whereNull('void')
            ->orderBy('intitule');

        // Filtre par catégorie
        if ($request->filled('categorie')) {
            $query->where('idcategorie', $request->categorie);
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Recherche par nom
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('intitule', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $menus      = $query->paginate(12)->withQueryString();
        $categories = Categorie::where('statut', 'Activé')
                               ->whereNull('void')
                               ->orderBy('intitule')
                               ->get();

        // Compteurs résumé
        $totalActifs    = Menu::where('statut', 'Activé')->whereNull('void')->count();
        $totalInactifs  = Menu::where('statut', 'Désactivé')->whereNull('void')->count();

        return view('menu.index', compact(
            'menus',
            'categories',
            'totalActifs',
            'totalInactifs'
        ));
    }

    // =========================================================
    // FORMULAIRE CRÉATION
    // =========================================================

    public function create()
    {
        $categories = Categorie::where('statut', 'Activé')
                               ->whereNull('void')
                               ->orderBy('intitule')
                               ->get();

        return view('menu.create', compact('categories'));
    }

    // =========================================================
    // ENREGISTRER UN NOUVEAU MENU
    // =========================================================

    public function store(MenuRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Traitement de la photo
            if ($request->hasFile('photo')) {
                $data['photo'] = $this->uploadPhoto($request->file('photo'));
            }

            $menu = Menu::create($data);

            DB::commit();

            Log::info('Menu créé', [
                'menu' => $menu->intitule,
                'user' => Auth::user()->email,
            ]);

            return redirect()->route('admin.menus.index')
                ->with('success', "Le plat « {$menu->intitule} » a été créé avec succès !");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création menu', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    // =========================================================
    // DÉTAIL D'UN MENU
    // =========================================================

    public function show(Menu $menu)
    {
        if ($menu->void) {
            abort(404);
        }

        $menu->load('categorie');

        // Statistiques de ventes du plat
        $totalVendu = DB::table('lignes')
            ->join('commandes', 'lignes.idcommande', '=', 'commandes.idcommande')
            ->where('lignes.idmenu', $menu->idmenu)
            ->whereNull('commandes.void')
            ->sum('lignes.quantite');

        $caTotal = DB::table('lignes')
            ->join('commandes', 'lignes.idcommande', '=', 'commandes.idcommande')
            ->where('lignes.idmenu', $menu->idmenu)
            ->whereNull('commandes.void')
            ->sum('lignes.prix');

        // Historique des 30 derniers jours
        $ventesMois = DB::table('lignes')
            ->join('commandes', 'lignes.idcommande', '=', 'commandes.idcommande')
            ->where('lignes.idmenu', $menu->idmenu)
            ->whereNull('commandes.void')
            ->where('commandes.datecommande', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(commandes.datecommande) as date'),
                DB::raw('SUM(lignes.quantite) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('menu.show', compact(
            'menu',
            'totalVendu',
            'caTotal',
            'ventesMois'
        ));
    }

    // =========================================================
    // FORMULAIRE MODIFICATION
    // =========================================================

    public function edit(Menu $menu)
    {
        if ($menu->void) {
            abort(404);
        }

        $categories = Categorie::where('statut', 'Activé')
                               ->whereNull('void')
                               ->orderBy('intitule')
                               ->get();

        return view('menu.edit', compact('menu', 'categories'));
    }

    // =========================================================
    // METTRE À JOUR UN MENU
    // =========================================================

    public function update(MenuRequest $request, Menu $menu)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Traitement de la photo
            if ($request->hasFile('photo')) {
                // Supprimer l'ancienne photo
                $this->supprimerPhoto($menu->photo);
                $data['photo'] = $this->uploadPhoto($request->file('photo'));
            }

            // Si pas de nouvelle photo, conserver l'ancienne
            if (!$request->hasFile('photo')) {
                unset($data['photo']);
            }

            $menu->update($data);

            DB::commit();

            Log::info('Menu modifié', [
                'menu' => $menu->intitule,
                'user' => Auth::user()->email,
            ]);

            return redirect()->route('admin.menus.index')
                ->with('success', "Le plat « {$menu->intitule} » a été modifié avec succès !");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur modification menu', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la modification : ' . $e->getMessage());
        }
    }

    // =========================================================
    // SUPPRIMER UN MENU (Soft Delete)
    // =========================================================

    public function destroy(Menu $menu)
    {
        // Vérifier si le plat est dans des commandes actives
        $commandesActives = DB::table('lignes')
            ->join('commandes', 'lignes.idcommande', '=', 'commandes.idcommande')
            ->where('lignes.idmenu', $menu->idmenu)
            ->whereNotIn('commandes.statut_courant', ['Servie', 'Livrée', 'Annulée'])
            ->whereNull('commandes.void')
            ->count();

        if ($commandesActives > 0) {
            return back()->with('error',
                "Impossible de supprimer « {$menu->intitule} » : il est dans {$commandesActives} commande(s) active(s)."
            );
        }

        DB::beginTransaction();

        try {
            // Soft delete via le champ void
            $menu->update([
                'void'       => '1',
                'statut'     => 'Désactivé',
                'deleted_at' => now(),
            ]);

            DB::commit();

            Log::info('Menu supprimé', [
                'menu' => $menu->intitule,
                'user' => Auth::user()->email,
            ]);

            return redirect()->route('admin.menus.index')
                ->with('success', "Le plat « {$menu->intitule} » a été supprimé.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression menu', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    // =========================================================
    // ACTIVER / DÉSACTIVER EN UN CLIC
    // =========================================================

    public function toggleStatut(Menu $menu)
    {
        $nouveauStatut = $menu->statut === 'Activé' ? 'Désactivé' : 'Activé';

        $menu->update(['statut' => $nouveauStatut]);

        Log::info('Statut menu modifié', [
            'menu'   => $menu->intitule,
            'statut' => $nouveauStatut,
            'user'   => Auth::user()->email,
        ]);

        // Réponse JSON si appel AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'statut'  => $nouveauStatut,
                'message' => "« {$menu->intitule} » est maintenant {$nouveauStatut}.",
            ]);
        }

        return back()->with(
            'success',
            "« {$menu->intitule} » est maintenant {$nouveauStatut}."
        );
    }

    // =========================================================
    // SUPPRESSION DE LA PHOTO UNIQUEMENT
    // =========================================================

    public function supprimerPhotoMenu(Menu $menu)
    {
        if (!$menu->photo) {
            return back()->with('error', 'Ce plat n\'a pas de photo.');
        }

        $this->supprimerPhoto($menu->photo);
        $menu->update(['photo' => null]);

        return back()->with('success', 'Photo supprimée avec succès.');
    }

    // =========================================================
    // LISTE POUR L'API (appel AJAX depuis prise de commande)
    // =========================================================

    public function listeActive(Request $request)
    {
        $query = Menu::with('categorie')
            ->where('statut', 'Activé')
            ->whereNull('void');

        if ($request->filled('idcategorie')) {
            $query->where('idcategorie', $request->idcategorie);
        }

        if ($request->filled('q')) {
            $query->where('intitule', 'like', '%' . $request->q . '%');
        }

        $menus = $query->orderBy('intitule')->get()->map(function ($menu) {
            return [
                'idmenu'      => $menu->idmenu,
                'intitule'    => $menu->intitule,
                'description' => $menu->description,
                'pu'          => $menu->pu,
                'photo_url'   => $menu->photo_url,
                'categorie'   => $menu->categorie->intitule ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $menus,
            'count'   => $menus->count(),
        ]);
    }

    // =========================================================
    // MÉTHODES PRIVÉES
    // =========================================================

    /**
     * Uploader et stocker une photo de plat
     * Retourne le chemin relatif stocké en base
     */
    private function uploadPhoto($file): string
    {
        // Générer un nom unique
        $nomFichier = uniqid('plat_') . '_' . time() . '.' . $file->getClientOriginalExtension();

        // Stocker dans storage/app/public/menus/photos/
        return $file->storeAs('menus/photos', $nomFichier, 'public');
    }

    /**
     * Supprimer une photo du stockage
     */
    private function supprimerPhoto(?string $chemin): void
    {
        if ($chemin && Storage::disk('public')->exists($chemin)) {
            Storage::disk('public')->delete($chemin);
        }
    }
}