<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Parametre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ParametreController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // VALEURS PAR DÉFAUT lors de la création initiale
    // ══════════════════════════════════════════════════════════════

    private const DEFAULTS = [
        'nom_restaurant'   => 'Chez Clarence',
        'slogan'           => '',
        'description'      => '',
        'adresse'          => '',
        'telephone'        => '',
        'telephone2'       => '',
        'email'            => '',
        'ville'            => 'Douala',
        'horaires'         => '',
        'whatsapp'         => '',
        'message_whatsapp' => 'Bonjour ! Je souhaite passer une commande.',
        'devise'           => 'FCFA',
        'tva'              => 0,
        'prefixe_recu'     => 'CC',
        'pied_recu'        => 'Merci pour votre visite !',
        'mention_legale'   => '',
        'logo'             => null,
    ];

    // ══════════════════════════════════════════════════════════════
    // HELPER PRIVÉ : récupérer ou créer l'enregistrement unique
    // ══════════════════════════════════════════════════════════════

    private function getParametre(): Parametre
    {
        return Parametre::firstOrCreate(
            ['idparametres' => 1],
            self::DEFAULTS
        );
    }

    // ══════════════════════════════════════════════════════════════
    // INDEX — afficher la page des paramètres
    // GET /parametres
    // ══════════════════════════════════════════════════════════════

    public function index()
    {
        $parametre = $this->getParametre();

        // [MODIFIÉ] diagnostic déplacé ici plutôt que calculé en
        // inline dans la vue — logique métier, pas de présentation.
        $lienOk      = $this->lienStockageFonctionnel();
        $lienExiste  = file_exists(public_path('storage'));
        $dossierFige = $lienExiste && !$lienOk;
        $hotPresent  = file_exists(public_path('hot'));

        return view('parametre.index', compact(
            'parametre',
            'lienOk',
            'dossierFige',
            'hotPresent'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    // SHOW — retourner les paramètres en JSON (pour appels AJAX)
    // GET /parametres/json
    // ══════════════════════════════════════════════════════════════

    public function show(Request $request)
    {
        if (!$request->expectsJson()) {
            return redirect()->route('admin.parametres.index');
        }

        $parametre = $this->getParametre();

        return response()->json([
            'success'   => true,
            'parametre' => $parametre,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // UPDATE — enregistrer les modifications
    // PUT /parametres
    // Reçoit un champ "section" pour savoir quel bloc a été soumis
    // ══════════════════════════════════════════════════════════════

    public function update(Request $request)
    {
        $section   = $request->input('section');
        $parametre = $this->getParametre();

        match ($section) {
            'identite'    => $this->updateIdentite($request, $parametre),
            'coordonnees' => $this->updateCoordonnees($request, $parametre),
            'whatsapp'    => $this->updateWhatsapp($request, $parametre),
            'caisse'      => $this->updateCaisse($request, $parametre),
            default       => null,
        };

        return redirect()
            ->route('admin.parametres.index')
            ->with('success', $this->messageSucces($section));
    }

    // ══════════════════════════════════════════════════════════════
    // SUPPRIMER LOGO
    // DELETE /parametres/logo
    // ══════════════════════════════════════════════════════════════

    public function supprimerLogo()
    {
        $parametre = $this->getParametre();

        if ($parametre->logo) {
            Storage::disk('public')->delete($parametre->logo);
            $parametre->update(['logo' => null]);
        }

        return redirect()
            ->route('admin.parametres.index')
            ->with('success', 'Logo supprimé.');
    }

    // ══════════════════════════════════════════════════════════════
    // VIDER LE CACHE
    // POST /parametres/vider-cache
    // ══════════════════════════════════════════════════════════════

    public function viderCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');

            return redirect()
                ->route('admin.parametres.index')
                ->with('success', 'Cache vidé avec succès.');

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.parametres.index')
                ->with('error', 'Erreur lors du vidage du cache : ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════
    // NETTOYER LE FICHIER VITE "hot"
    // POST /parametres/nettoyer-hot
    //
    // [POURQUOI] ce fichier est créé automatiquement par `npm run
    // dev` en développement. S'il se retrouve déployé par erreur en
    // production (git push, FTP, résidu d'un ancien déploiement...),
    // Laravel charge alors TOUJOURS les assets depuis le serveur Vite
    // de développement (ex. http://[::1]:5173) au lieu des fichiers
    // compilés dans public/build — ce qui casse le site avec des
    // erreurs CORS pour tous les visiteurs, sur toutes les pages.
    // ══════════════════════════════════════════════════════════════

    public function nettoyerFichierHot()
    {
        $chemin = public_path('hot');

        if (!file_exists($chemin)) {
            return redirect()
                ->route('admin.parametres.index')
                ->with('success', 'Aucun fichier "hot" détecté — rien à nettoyer, tout est normal.');
        }

        try {
            unlink($chemin);

            return redirect()
                ->route('admin.parametres.index')
                ->with('success', 'Fichier Vite "hot" supprimé. Le site utilisera désormais les assets compilés (public/build).');

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.parametres.index')
                ->with('error', 'Impossible de supprimer le fichier "hot" (permissions ?) : ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════
    // RECRÉER LE LIEN DE STOCKAGE (public/storage)
    // POST /parametres/lien-stockage
    //
    // [POURQUOI] sans ce lien symbolique, toutes les images uploadées
    // (logo, photos de plats/catégories) affichent une image cassée
    // — fréquent après un déploiement sur un nouveau serveur, où le
    // lien n'a jamais été créé.
    // ══════════════════════════════════════════════════════════════

    public function recreerLienStockage()
    {
        $lien = public_path('storage');

        try {
            $dejaFonctionnel = $this->lienStockageFonctionnel(utiliserCache: false);

            if (!$dejaFonctionnel && file_exists($lien)) {
                // Présent mais non fonctionnel : dossier figé (copie
                // manuelle ayant suivi le lien/la jonction au moment de
                // la copie) ou lien cassé. Dans les deux cas il faut le
                // supprimer, sinon storage:link se contente de dire
                // "le lien existe déjà" sans rien corriger.
                if (is_link($lien)) {
                    unlink($lien);
                } elseif (is_dir($lien)) {
                    File::deleteDirectory($lien);
                } else {
                    @unlink($lien);
                }
            }

            if (!$dejaFonctionnel) {
                Artisan::call('storage:link');
            }

            Cache::forget('parametre_lien_stockage_ok');
            $ok = $this->lienStockageFonctionnel(utiliserCache: false);

            if ($ok) {
                return redirect()
                    ->route('admin.parametres.index')
                    ->with('success', $dejaFonctionnel
                        ? 'Lien de stockage déjà fonctionnel — rien à corriger.'
                        : 'Ancien dossier figé nettoyé si nécessaire, et lien de stockage recréé avec succès.'
                    );
            }

            // [OS] message contextualisé — la cause probable diffère
            // sensiblement entre Windows et Linux/macOS.
            $conseil = match (PHP_OS_FAMILY) {
                'Windows' => 'Sur Windows, la création de la jonction (mklink) nécessite généralement des droits administrateur. Essayez d\'exécuter "php artisan storage:link" depuis un terminal lancé en tant qu\'administrateur.',
                default   => 'Vérifiez que le processus PHP/serveur web a le droit de créer des liens symboliques dans public/, et que cette fonctionnalité n\'est pas désactivée par votre hébergeur (fréquent sur certains mutualisés Linux).',
            };

            return redirect()
                ->route('admin.parametres.index')
                ->with('error', "Le lien n'a pas pu être établi (environnement détecté : " . PHP_OS_FAMILY . "). {$conseil}");

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.parametres.index')
                ->with('error', 'Erreur lors de la création du lien : ' . $e->getMessage());
        }
    }

    /**
     * Vérifie si le lien/la jonction public/storage → storage/app/public
     * fonctionne réellement, indépendamment de l'OS et du mécanisme
     * technique sous-jacent (symlink Unix, jonction NTFS Windows...).
     *
     * [OS] Écrit un fichier marqueur dans le vrai dossier de stockage
     * puis vérifie sa visibilité via le chemin public — plus fiable
     * que is_link(), notamment sur Windows où is_link() peut ne pas
     * reconnaître une jonction créée par 'mklink /J'.
     *
     * Résultat mis en cache 5 minutes pour éviter d'écrire/lire un
     * fichier à chaque affichage de la page (coût I/O), sauf appel
     * explicite avec utiliserCache: false (après une action corrective,
     * où l'état frais est indispensable).
     */
    private function lienStockageFonctionnel(bool $utiliserCache = true): bool
    {
        $verification = function (): bool {
            if (!is_dir(storage_path('app/public'))) {
                return false;
            }

            $marqueur     = '.test-lien-' . uniqid() . '.tmp';
            $cheminReel   = storage_path('app/public/' . $marqueur);
            $cheminPublic = public_path('storage/' . $marqueur);

            try {
                file_put_contents($cheminReel, 'ok');

                return file_exists($cheminPublic) && file_get_contents($cheminPublic) === 'ok';

            } catch (\Exception $e) {
                return false;

            } finally {
                if (file_exists($cheminReel)) {
                    @unlink($cheminReel);
                }
            }
        };

        if (!$utiliserCache) {
            return $verification();
        }

        return Cache::remember('parametre_lien_stockage_ok', 300, $verification);
    }

    // ══════════════════════════════════════════════════════════════
    // OPTIMISER POUR LA PRODUCTION (config + routes + vues en cache)
    // POST /parametres/optimiser
    // ══════════════════════════════════════════════════════════════

    public function optimiserProduction()
    {
        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');

            return redirect()
                ->route('admin.parametres.index')
                ->with('success', 'Application optimisée : configuration, routes et vues mises en cache pour de meilleures performances.');

        } catch (\Exception $e) {
            // Message actionnable si une route à closure est un jour
            // réintroduite (route:cache l'interdit systématiquement).
            $message = str_contains($e->getMessage(), 'Closure')
                ? 'Erreur : une route utilise une closure comme gestionnaire, ce que route:cache interdit. Remplacez-la par un contrôleur, ou effectuez "Vider le cache" pour repartir sur une base propre.'
                : 'Erreur lors de l\'optimisation : ' . $e->getMessage();

            return redirect()
                ->route('admin.parametres.index')
                ->with('error', $message);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // MÉTHODES PRIVÉES : mise à jour par section
    // ══════════════════════════════════════════════════════════════

    /**
     * Section "Identité" : nom, slogan, description, logo
     */
    private function updateIdentite(Request $request, Parametre $parametre): void
    {
        $data = $request->validate([
            'nom_restaurant' => ['required', 'string', 'max:128'],
            'slogan'         => ['nullable', 'string', 'max:200'],
            'description'    => ['nullable', 'string', 'max:600'],
            'logo'           => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {

            // Supprimer l'ancien logo s'il existe
            if ($parametre->logo) {
                Storage::disk('public')->delete($parametre->logo);
            }

            $nomFichier = 'logo_' . time() . '_' . Str::random(6)
                . '.' . $request->file('logo')->getClientOriginalExtension();

            $data['logo'] = $request->file('logo')
                ->storeAs('logos', $nomFichier, 'public');
        } else {
            unset($data['logo']);
        }

        $parametre->update($data);
    }

    /**
     * Section "Coordonnées" : adresse, téléphones, email, ville, horaires
     */
    private function updateCoordonnees(Request $request, Parametre $parametre): void
    {
        $data = $request->validate([
            'adresse'    => ['nullable', 'string', 'max:300'],
            'telephone'  => ['nullable', 'string', 'max:20'],
            'telephone2' => ['nullable', 'string', 'max:20'],
            'email'      => ['nullable', 'email', 'max:150'],
            'ville'      => ['nullable', 'string', 'max:100'],
            'horaires'   => ['nullable', 'string', 'max:200'],
        ]);

        $parametre->update($data);
    }

    /**
     * Section "WhatsApp" : numéro et message d'accueil
     * Le numéro est nettoyé (chiffres et + uniquement) pour les liens wa.me
     */
    private function updateWhatsapp(Request $request, Parametre $parametre): void
    {
        $data = $request->validate([
            'whatsapp'         => ['nullable', 'string', 'max:20'],
            'message_whatsapp' => ['nullable', 'string', 'max:500'],
        ]);

        // Nettoyer le numéro : ne garder que chiffres et +
        if (!empty($data['whatsapp'])) {
            $data['whatsapp'] = preg_replace('/[^\d+]/', '', $data['whatsapp']);
        }

        $parametre->update($data);
    }

    /**
     * Section "Caisse" : devise, TVA, préfixe reçu, pied de reçu, mention légale
     */
    private function updateCaisse(Request $request, Parametre $parametre): void
    {
        $data = $request->validate([
            'devise'         => ['required', 'string', 'in:FCFA,EUR,USD,XOF'],
            'tva'            => ['nullable', 'numeric', 'min:0', 'max:100'],
            'prefixe_recu'   => ['nullable', 'string', 'max:5'],
            'pied_recu'      => ['nullable', 'string', 'max:300'],
            'mention_legale' => ['nullable', 'string', 'max:200'],
        ]);

        // Convertir tva en float (null → 0)
        $data['tva'] = (float) ($data['tva'] ?? 0);

        $parametre->update($data);
    }

    // ══════════════════════════════════════════════════════════════
    // HELPER PRIVÉ : message de succès selon la section
    // ══════════════════════════════════════════════════════════════

    private function messageSucces(string $section): string
    {
        return match ($section) {
            'identite'    => 'Identité du restaurant enregistrée.',
            'coordonnees' => 'Coordonnées enregistrées.',
            'whatsapp'    => 'Paramètres WhatsApp enregistrés.',
            'caisse'      => 'Paramètres de caisse enregistrés.',
            default       => 'Paramètres enregistrés.',
        };
    }
}