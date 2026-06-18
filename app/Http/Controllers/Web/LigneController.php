<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ligne;
use App\Models\Commande;
use App\Models\Menu;
use App\Models\Historique;
use App\Models\Statut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LigneController extends Controller
{
    // =========================================================
    // AJOUTER UN ARTICLE À UNE COMMANDE EXISTANTE
    // Cas d'usage : le client veut rajouter un plat en cours de repas
    // =========================================================

    public function store(Request $request, Commande $commande)
    {
        // Vérifier que la commande est encore modifiable
        if (!$commande->estModifiable()) {
            return $this->repondre(false,
                "La commande {$commande->reference} ne peut plus être modifiée (statut : {$commande->statut_courant}).",
                422
            );
        }

        $request->validate([
            'idmenu'   => 'required|exists:menus,idmenu',
            'quantite' => 'required|integer|min:1|max:99',
            'remise'   => 'nullable|numeric|min:0',
        ], [
            'idmenu.required'   => 'Veuillez sélectionner un plat.',
            'idmenu.exists'     => 'Ce plat n\'existe pas.',
            'quantite.required' => 'La quantité est obligatoire.',
            'quantite.min'      => 'La quantité minimale est 1.',
            'quantite.max'      => 'La quantité maximale est 99.',
            'remise.min'        => 'La remise ne peut pas être négative.',
        ]);

        DB::beginTransaction();

        try {
            // Vérifier que le plat est actif et disponible
            $menu = Menu::where('idmenu', $request->idmenu)
                ->where('statut', 'Activé')
                ->whereNull('void')
                ->first();

            if (!$menu) {
                DB::rollBack();
                return $this->repondre(false,
                    'Ce plat n\'est plus disponible ou a été désactivé.',
                    422
                );
            }

            $remise   = (float) ($request->remise ?? 0);
            $quantite = (int) $request->quantite;

            // Vérifier si ce plat est déjà dans la commande
            // Si oui, incrémenter la quantité plutôt que créer une nouvelle ligne
            $ligneExistante = Ligne::where('idcommande', $commande->idcommande)
                ->where('idmenu', $menu->idmenu)
                ->first();

            if ($ligneExistante) {
                $nouvelleQuantite = $ligneExistante->quantite + $quantite;
                $nouveauPrix      = Ligne::calculerPrix($menu->pu, $nouvelleQuantite, $remise);

                $ligneExistante->update([
                    'quantite' => $nouvelleQuantite,
                    'remise'   => $remise,
                    'prix'     => $nouveauPrix,
                ]);

                $ligne = $ligneExistante;
                $action = 'incrémentée';
            } else {
                // Créer une nouvelle ligne
                $prix = Ligne::calculerPrix($menu->pu, $quantite, $remise);

                $ligne = Ligne::create([
                    'idcommande' => $commande->idcommande,
                    'idmenu'     => $menu->idmenu,
                    'quantite'   => $quantite,
                    'remise'     => $remise,
                    'prix'       => $prix,
                ]);

                $action = 'ajoutée';
            }

            // Recalculer le montant total de la commande
            $this->recalculerMontant($commande);

            DB::commit();

            Log::info("Ligne {$action} à la commande", [
                'commande' => $commande->reference,
                'menu'     => $menu->intitule,
                'quantite' => $quantite,
                'user'     => Auth::user()->email,
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success'  => true,
                    'message'  => "« {$menu->intitule} » {$action} à la commande {$commande->reference}.",
                    'ligne'    => [
                        'idligne'  => $ligne->idligne,
                        'intitule' => $menu->intitule,
                        'quantite' => $ligne->quantite,
                        'prix'     => $ligne->prix,
                        'pu'       => $menu->pu,
                    ],
                    'nouveau_montant' => $commande->fresh()->montant,
                ]);
            }

            return back()->with('success',
                "« {$menu->intitule} » ajouté à la commande {$commande->reference}."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur ajout ligne commande', ['error' => $e->getMessage()]);
            return $this->repondre(false, 'Erreur : ' . $e->getMessage(), 500);
        }
    }

    // =========================================================
    // MODIFIER LA QUANTITÉ D'UNE LIGNE
    // Cas d'usage : correction rapide avant envoi en cuisine
    // =========================================================

    public function update(Request $request, Commande $commande, Ligne $ligne)
    {
        // Vérifier que la ligne appartient bien à cette commande
        if ($ligne->idcommande !== $commande->idcommande) {
            return $this->repondre(false, 'Cette ligne n\'appartient pas à cette commande.', 403);
        }

        // Vérifier que la commande est encore modifiable
        if (!$commande->estModifiable()) {
            return $this->repondre(false,
                "La commande {$commande->reference} ne peut plus être modifiée.",
                422
            );
        }

        $request->validate([
            'quantite' => 'required|integer|min:1|max:99',
            'remise'   => 'nullable|numeric|min:0',
        ], [
            'quantite.required' => 'La quantité est obligatoire.',
            'quantite.min'      => 'La quantité minimale est 1.',
            'quantite.max'      => 'La quantité maximale est 99.',
        ]);

        DB::beginTransaction();

        try {
            $menu     = Menu::find($ligne->idmenu);
            $quantite = (int) $request->quantite;
            $remise   = (float) ($request->remise ?? $ligne->remise ?? 0);
            $prix     = Ligne::calculerPrix($menu->pu, $quantite, $remise);

            $ligne->update([
                'quantite' => $quantite,
                'remise'   => $remise,
                'prix'     => $prix,
            ]);

            // Recalculer le montant total de la commande
            $this->recalculerMontant($commande);

            DB::commit();

            Log::info('Ligne commande modifiée', [
                'commande' => $commande->reference,
                'menu'     => $menu->intitule ?? 'N/A',
                'quantite' => $quantite,
                'user'     => Auth::user()->email,
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success'         => true,
                    'message'         => 'Quantité mise à jour.',
                    'ligne'           => [
                        'idligne'  => $ligne->idligne,
                        'quantite' => $ligne->quantite,
                        'prix'     => $ligne->prix,
                        'remise'   => $ligne->remise,
                    ],
                    'nouveau_montant' => $commande->fresh()->montant,
                ]);
            }

            return back()->with('success', 'Quantité mise à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur modification ligne', ['error' => $e->getMessage()]);
            return $this->repondre(false, 'Erreur : ' . $e->getMessage(), 500);
        }
    }

    // =========================================================
    // SUPPRIMER UNE LIGNE D'UNE COMMANDE
    // Cas d'usage : retirer un article avant envoi en cuisine
    // =========================================================

    public function destroy(Commande $commande, Ligne $ligne)
    {
        // Vérifier que la ligne appartient à cette commande
        if ($ligne->idcommande !== $commande->idcommande) {
            return $this->repondre(false, 'Cette ligne n\'appartient pas à cette commande.', 403);
        }

        // Vérifier que la commande est modifiable
        if (!$commande->estModifiable()) {
            return $this->repondre(false,
                "La commande {$commande->reference} ne peut plus être modifiée.",
                422
            );
        }

        // Interdire de vider complètement une commande (minimum 1 article)
        $nbLignes = Ligne::where('idcommande', $commande->idcommande)->count();

        if ($nbLignes <= 1) {
            return $this->repondre(false,
                'Impossible de supprimer le dernier article. Annulez la commande si nécessaire.',
                422
            );
        }

        DB::beginTransaction();

        try {
            $menu = Menu::find($ligne->idmenu);
            $ligne->delete();

            // Recalculer le montant total
            $this->recalculerMontant($commande);

            DB::commit();

            Log::info('Ligne supprimée de la commande', [
                'commande' => $commande->reference,
                'menu'     => $menu->intitule ?? 'N/A',
                'user'     => Auth::user()->email,
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success'         => true,
                    'message'         => 'Article retiré de la commande.',
                    'nouveau_montant' => $commande->fresh()->montant,
                ]);
            }

            return back()->with('success', 'Article retiré de la commande.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression ligne', ['error' => $e->getMessage()]);
            return $this->repondre(false, 'Erreur : ' . $e->getMessage(), 500);
        }
    }

    // =========================================================
    // APPLIQUER UNE REMISE SUR UNE LIGNE
    // Cas d'usage : geste commercial sur un article précis
    // =========================================================

    public function appliquerRemise(Request $request, Commande $commande, Ligne $ligne)
    {
        // Seul Admin et Caissier peuvent appliquer des remises
        if (!in_array(Auth::user()->role, ['Administrateur', 'Caissier'])) {
            return $this->repondre(false, 'Vous n\'avez pas les droits pour appliquer une remise.', 403);
        }

        if ($ligne->idcommande !== $commande->idcommande) {
            return $this->repondre(false, 'Cette ligne n\'appartient pas à cette commande.', 403);
        }

        $request->validate([
            'remise' => 'required|numeric|min:0',
        ], [
            'remise.required' => 'Le montant de la remise est obligatoire.',
            'remise.min'      => 'La remise ne peut pas être négative.',
        ]);

        DB::beginTransaction();

        try {
            $menu   = Menu::find($ligne->idmenu);
            $remise = (float) $request->remise;

            // Vérifier que la remise ne dépasse pas le prix de la ligne
            $prixBrut = $menu->pu * $ligne->quantite;
            if ($remise >= $prixBrut) {
                DB::rollBack();
                return $this->repondre(false,
                    "La remise ({$remise} FCFA) ne peut pas dépasser le prix de la ligne ({$prixBrut} FCFA).",
                    422
                );
            }

            $nouveauPrix = Ligne::calculerPrix($menu->pu, $ligne->quantite, $remise);

            $ligne->update([
                'remise' => $remise,
                'prix'   => $nouveauPrix,
            ]);

            $this->recalculerMontant($commande);

            DB::commit();

            Log::info('Remise appliquée sur ligne', [
                'commande' => $commande->reference,
                'menu'     => $menu->intitule ?? 'N/A',
                'remise'   => $remise,
                'user'     => Auth::user()->email,
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success'         => true,
                    'message'         => "Remise de {$remise} FCFA appliquée.",
                    'nouveau_prix'    => $nouveauPrix,
                    'nouveau_montant' => $commande->fresh()->montant,
                ]);
            }

            return back()->with('success',
                "Remise de " . number_format($remise, 0, ',', ' ') . " FCFA appliquée."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur application remise', ['error' => $e->getMessage()]);
            return $this->repondre(false, 'Erreur : ' . $e->getMessage(), 500);
        }
    }

    // =========================================================
    // LISTE DES LIGNES D'UNE COMMANDE (AJAX)
    // =========================================================

    public function liste(Commande $commande)
    {
        $lignes = Ligne::where('idcommande', $commande->idcommande)
            ->with('menu.categorie')
            ->get()
            ->map(function ($ligne) {
                return [
                    'idligne'    => $ligne->idligne,
                    'idmenu'     => $ligne->idmenu,
                    'intitule'   => $ligne->menu->intitule ?? 'N/A',
                    'categorie'  => $ligne->menu->categorie->intitule ?? 'N/A',
                    'photo_url'  => $ligne->menu->photo_url ?? null,
                    'pu'         => $ligne->menu->pu ?? 0,
                    'quantite'   => $ligne->quantite,
                    'remise'     => $ligne->remise,
                    'prix'       => $ligne->prix,
                    'prix_fmt'   => number_format($ligne->prix, 0, ',', ' ') . ' FCFA',
                ];
            });

        return response()->json([
            'success'        => true,
            'data'           => $lignes,
            'total'          => $lignes->count(),
            'montant_total'  => $commande->montant,
            'montant_format' => number_format($commande->montant, 0, ',', ' ') . ' FCFA',
        ]);
    }

    // =========================================================
    // MÉTHODES PRIVÉES
    // =========================================================

    /**
     * Recalculer et mettre à jour le montant total de la commande
     * en faisant la somme de toutes ses lignes
     */
    private function recalculerMontant(Commande $commande): void
    {
        $nouveauMontant = Ligne::where('idcommande', $commande->idcommande)
            ->sum('prix');

        $commande->update(['montant' => $nouveauMontant]);
    }

    /**
     * Répondre en JSON ou en redirect selon le type de requête
     */
    private function repondre(bool $success, string $message, int $code = 200)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
            ], $code);
        }

        $type = $success ? 'success' : 'error';
        return back()->with($type, $message);
    }
}