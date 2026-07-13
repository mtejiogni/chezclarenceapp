<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Parametre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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

        return view('parametre.index', compact('parametre'));
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
    // Ce fichier est créé automatiquement par `npm run dev`
    // en développement. S'il se retrouve déployé par erreur en
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
    // Sans ce lien symbolique, toutes les images uploadées
    // (logo, photos de plats/catégories) affichent une image cassée
    // — fréquent après un déploiement sur un nouveau serveur, où le
    // lien n'a jamais été créé.
    // ══════════════════════════════════════════════════════════════

    public function recreerLienStockage()
    {
        try {
            Artisan::call('storage:link');

            $ok = file_exists(public_path('storage'));

            return redirect()
                ->route('admin.parametres.index')
                ->with($ok ? 'success' : 'error', $ok
                    ? 'Lien de stockage créé/vérifié avec succès.'
                    : 'La commande s\'est exécutée mais le lien semble toujours absent — vérifiez les permissions du dossier public/ sur le serveur.'
                );

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.parametres.index')
                ->with('error', 'Erreur lors de la création du lien : ' . $e->getMessage());
        }
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