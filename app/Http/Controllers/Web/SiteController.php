<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Models\Parametre;
use Illuminate\Http\Request;

// ══════════════════════════════════════════════════════════════
// SiteController — contrôleur du site public (vitrine du restaurant).
// Destiné à contenir toutes les requêtes du site public ; ne gère
// pour l'instant que la page vitrine (single page), mais d'autres
// méthodes publiques (ex: mentions légales, page d'un plat, etc.)
// peuvent y être ajoutées par la suite.
//
// S'appuie entièrement sur les modèles déjà existants du projet :
//   - Parametre::config()            → infos du restaurant
//   - Categorie::actives()           → scope déjà défini sur Categorie
//   - $categorie->menusActifs        → relation déjà filtrée (Activé + non void)
//   - $menu->nb_commandes            → accesseur déjà défini sur Menu
//   - $menu->photo_url / $categorie->photo_url / $parametres->logo_url
//     → accesseurs déjà définis, avec image par défaut si absente
//
// Aucun modèle n'est modifié : ce contrôleur ne fait que les consommer.
// ══════════════════════════════════════════════════════════════

class SiteController extends Controller
{
    /**
     * Page d'accueil du site public (single page) : hero, présentation,
     * menu filtrable, services, localisation, contact et chatbot WhatsApp.
     *
     * GET / → resources/views/site/index.blade.php
     */
    public function index(Request $request)
    {
        $parametres = Parametre::config();

        $categories = Categorie::actives()
            ->with('menusActifs')
            ->orderBy('intitule')
            ->get()
            ->filter(fn ($categorie) => $categorie->menusActifs->isNotEmpty())
            ->values();

        // ── Étoiles : basées sur nb_commandes (accesseur du modèle Menu) ──
        $totauxParMenu = [];
        foreach ($categories as $categorie) {
            foreach ($categorie->menusActifs as $menu) {
                $totauxParMenu[$menu->idmenu] = $menu->nb_commandes;
            }
        }
        $maxCommandes = $totauxParMenu ? max($totauxParMenu) : 0;

        foreach ($categories as $categorie) {
            foreach ($categorie->menusActifs as $menu) {
                // "etoiles" est un attribut ad hoc (pas d'accesseur du même nom
                // sur le modèle), donc il est bien sérialisé par @js() côté vue.
                $menu->etoiles = $this->calculerEtoiles($totauxParMenu[$menu->idmenu], $maxCommandes);
            }
        }

        // ── Bornes de prix réellement disponibles, pour le filtre du menu ──
        $tousLesPrix = $categories->flatMap->menusActifs
            ->pluck('pu')
            ->map(fn ($pu) => (float) $pu)
            ->filter();

        $prixMin = $tousLesPrix->isNotEmpty() ? (int) floor($tousLesPrix->min()) : 0;
        $prixMax = $tousLesPrix->isNotEmpty() ? (int) ceil($tousLesPrix->max()) : 10000;

        // ── Liens WhatsApp pré-remplis selon l'intention du client ──
        $whatsapp = $this->liensWhatsapp($parametres);

        // ── Lien Google Maps pour lancer un itinéraire GPS ──
        $lienItineraire = ($parametres->latitude && $parametres->longitude)
            ? 'https://www.google.com/maps/dir/?api=1&destination='.$parametres->latitude.','.$parametres->longitude
            : '#';

        // ── Services annexes (aucune table dédiée dans le schéma) ──
        $services = $this->services($whatsapp['construire']);
        unset($whatsapp['construire']); // usage interne uniquement, inutile côté vue

        return view('site.index', [
            'parametres'     => $parametres,
            'categories'     => $categories,
            'services'       => $services,
            'prixMin'        => $prixMin,
            'prixMax'        => $prixMax,
            'whatsapp'       => $whatsapp,
            'lienItineraire' => $lienItineraire,
        ]);
    }

    /**
     * Convertit un volume de commandes en note de 1 à 5 étoiles,
     * relative au plat le plus commandé du restaurant.
     */
    private function calculerEtoiles(int $total, int $max): int
    {
        if ($max <= 0 || $total <= 0) {
            return 3; // note neutre pour les plats sans historique
        }

        $ratio = $total / $max;

        return match (true) {
            $ratio >= 0.8 => 5,
            $ratio >= 0.6 => 4,
            $ratio >= 0.35 => 3,
            $ratio >= 0.15 => 2,
            default => 1,
        };
    }

    /**
     * Construit les liens wa.me pré-remplis pour chaque intention
     * (commander, réserver, traiteur, contact) à partir du numéro
     * WhatsApp (ou, à défaut, du téléphone principal) des paramètres.
     */
    private function liensWhatsapp(Parametre $parametres): array
    {
        $numero = preg_replace('/\D+/', '', $parametres->whatsapp ?: ($parametres->telephone ?? ''));

        $construire = function (string $message) use ($numero) {
            return $numero ? 'https://wa.me/'.$numero.'?text='.rawurlencode($message) : '#';
        };

        return [
            'numero' => $numero,
            'construire' => $construire,
            'commander' => $construire('Bonjour, je souhaite passer une commande.'),
            'reserver' => $construire('Bonjour, je souhaite réserver une table.'),
            'traiteur' => $construire('Bonjour, je souhaite en savoir plus sur le service traiteur.'),
            'contact' => $construire('Bonjour, je vous contacte au sujet du restaurant.'),
            'defaut' => $parametres->whatsapp_url,
        ];
    }

    /**
     * Liste des services annexes proposés par le restaurant.
     * (Pas de table dédiée dans le schéma fourni : définis ici.)
     */
    private function services(\Closure $construireLienWhatsapp): array
    {
        $definitions = [
            [
                'id' => 'privatisation',
                'icone' => 'cle',
                'titre' => 'Privatisation du restaurant',
                'resume' => 'Réservez toute la salle pour vos événements privés.',
                'description' => "Anniversaires, réunions d'entreprise, cérémonies familiales : privatisez tout ou partie du restaurant et profitez d'un service dédié, d'une décoration sur mesure et d'un menu adapté au nombre de convives.",
                'message_whatsapp' => 'Bonjour, je souhaite privatiser le restaurant pour un événement. Pouvez-vous me communiquer vos disponibilités et tarifs ?',
            ],
            [
                'id' => 'traiteur',
                'icone' => 'toque',
                'titre' => 'Service traiteur',
                'resume' => 'Nos plats livrés et dressés où que vous soyez.',
                'description' => "Mariages, deuils, séminaires, fêtes de famille : notre équipe se déplace avec son savoir-faire pour régaler vos invités, du dressage des buffets jusqu'au service à table.",
                'message_whatsapp' => 'Bonjour, je souhaite un devis pour une prestation traiteur. Voici les détails de mon événement : ',
            ],
            [
                'id' => 'carte-cadeau',
                'icone' => 'cadeau',
                'titre' => 'Carte cadeau',
                'resume' => 'Offrez un moment gourmand à vos proches.',
                'description' => "Faites plaisir sans vous tromper : nos cartes cadeaux, valables sur toute la carte, se personnalisent selon le montant et l'occasion (anniversaire, fête, remerciement).",
                'message_whatsapp' => 'Bonjour, je souhaite offrir une carte cadeau. Pouvez-vous me renseigner sur les montants disponibles ?',
            ],
            [
                'id' => 'livraison',
                'icone' => 'scooter',
                'titre' => 'Livraison express',
                'resume' => 'Vos plats livrés chauds, partout en ville.',
                'description' => 'Commandez depuis chez vous ou votre bureau : nos livreurs vous apportent vos plats préférés rapidement, en toute fraîcheur, avec un suivi de commande en temps réel.',
                'message_whatsapp' => 'Bonjour, je souhaite me faire livrer. Voici mon adresse et ma commande : ',
            ],
            [
                'id' => 'evenements',
                'icone' => 'ballons',
                'titre' => "Organisation d'événements",
                'resume' => 'Décoration et animation pour vos réceptions.',
                'description' => "Baptêmes, anniversaires, cérémonies de fin d'année : nous prenons en charge la décoration, l'animation et la restauration pour une réception clé en main.",
                'message_whatsapp' => "Bonjour, je souhaite organiser un événement chez vous. Pouvez-vous m'aider à planifier tout cela ?",
            ],
            [
                'id' => 'entreprise',
                'icone' => 'groupe',
                'titre' => 'Formule groupe & entreprise',
                'resume' => "Déjeuners d'affaires et séminaires sur mesure.",
                'description' => 'Des menus adaptés à votre budget et à votre emploi du temps pour vos déjeuners d\'affaires, séminaires et formations, avec facturation groupée possible.',
                'message_whatsapp' => "Bonjour, je souhaite une offre pour un déjeuner d'affaires / séminaire d'entreprise.",
            ],
        ];

        foreach ($definitions as &$service) {
            $service['lien_whatsapp'] = $construireLienWhatsapp($service['message_whatsapp']);
        }

        return $definitions;
    }
}