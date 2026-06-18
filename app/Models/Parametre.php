<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Parametre extends Model
{
    // ══════════════════════════════════════════════════════════════
    // CONFIGURATION DU MODÈLE
    // ══════════════════════════════════════════════════════════════

    protected $table      = 'parametres';
    protected $primaryKey = 'idparametres';

    // Les timestamps sont gérés manuellement dans la migration
    public $timestamps = true;

    // ── Casts ─────────────────────────────────────────────────────
    // Convertit automatiquement les champs au bon type PHP
    // "datetime" → objet Carbon (permet ->format(), ->diffForHumans()...)
    // "float"    → nombre décimal
    protected $casts = [
        'created_at' => 'datetime',  // ← corrige l'erreur format() on string
        'updated_at' => 'datetime',  // ← corrige l'erreur format() on string
        'tva'        => 'float',
    ];

    // ── Champs autorisés à l'écriture (mass assignment) ──────────
    protected $fillable = [
        // Identité
        'entreprise',
        'nom_restaurant',
        'slogan',
        'description',
        'logo',

        // Coordonnées
        'adresse',
        'latitude',
        'longitude',
        'telephone',
        'telephone2',
        'email',
        'ville',
        'horaires',

        // WhatsApp
        'whatsapp',
        'message_whatsapp',

        // Caisse & reçus
        'devise',
        'tva',
        'prefixe_recu',
        'pied_recu',
        'mention_legale',
    ];

    // ══════════════════════════════════════════════════════════════
    // MÉTHODE STATIQUE
    // Récupère l'enregistrement unique ou le crée avec les valeurs
    // par défaut. Toujours utiliser cette méthode plutôt que ::find(1)
    // pour garantir qu'un enregistrement existe toujours.
    //
    // Utilisation :  $p = Parametre::config();
    //                echo $p->nom_restaurant;
    // ══════════════════════════════════════════════════════════════

    public static function config(): self
    {
        return self::firstOrCreate(
            ['idparametres' => 1],
            [
                // Identité
                'entreprise'     => 'Chez Clarence',
                'nom_restaurant' => 'Chez Clarence',
                'slogan'         => '',
                'description'    => '',
                'logo'           => null,

                // Coordonnées
                'adresse'    => '',
                'latitude'   => '',
                'longitude'  => '',
                'telephone'  => '',
                'telephone2' => '',
                'email'      => '',
                'ville'      => 'Douala',
                'horaires'   => '',

                // WhatsApp
                'whatsapp'         => '',
                'message_whatsapp' => 'Bonjour ! Je souhaite passer une commande.',

                // Caisse
                'devise'         => 'FCFA',
                'tva'            => 0,
                'prefixe_recu'   => 'CC',
                'pied_recu'      => 'Merci pour votre visite !',
                'mention_legale' => '',
            ]
        );
    }

    // ══════════════════════════════════════════════════════════════
    // ACCESSEURS
    // Les accesseurs sont des propriétés calculées accessibles comme
    // des attributs normaux : $p->logo_url, $p->whatsapp_url...
    // ══════════════════════════════════════════════════════════════

    /**
     * URL complète du logo.
     * Retourne le logo uploadé ou un logo par défaut.
     *
     * Utilisation : $parametre->logo_url
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }

        return asset('img/logo-default.png');
    }

    /**
     * Lien WhatsApp prêt à l'emploi (numéro + message pré-rempli).
     * Utilise le champ "whatsapp" en priorité,
     * sinon le champ "telephone" comme fallback.
     *
     * Utilisation : $parametre->whatsapp_url
     * Exemple     : https://wa.me/237699000000?text=Bonjour+%21+Je+souhaite...
     */
    public function getWhatsappUrlAttribute(): string
    {
        // Priorité : numéro whatsapp dédié, sinon téléphone principal
        $source = $this->whatsapp ?: $this->telephone;
        $tel    = preg_replace('/[^\d]/', '', $source ?? '');

        if (!$tel) {
            return '#'; // pas de numéro configuré
        }

        $url = 'https://wa.me/' . $tel;

        if ($this->message_whatsapp) {
            $url .= '?text=' . urlencode($this->message_whatsapp);
        }

        return $url;
    }

    /**
     * Nom d'affichage du restaurant.
     * Retourne nom_restaurant s'il est renseigné, sinon entreprise.
     *
     * Utilisation : $parametre->nom_affichage
     */
    public function getNomAffichageAttribute(): string
    {
        return $this->nom_restaurant ?: ($this->entreprise ?: 'Restaurant');
    }

    /**
     * Vérifie si un logo est configuré.
     *
     * Utilisation : @if($parametre->a_logo) ... @endif
     */
    public function getALogoAttribute(): bool
    {
        return !empty($this->logo);
    }

    /**
     * Formate le taux de TVA pour l'affichage.
     * Retourne "Non applicable" si TVA = 0.
     *
     * Utilisation : $parametre->tva_label
     * Exemple     : "19,25 %" ou "Non applicable"
     */
    public function getTvaLabelAttribute(): string
    {
        if (!$this->tva || $this->tva == 0) {
            return 'Non applicable';
        }

        return number_format($this->tva, 2, ',', ' ') . ' %';
    }

    /**
     * Date de création formatée en français.
     *
     * Utilisation : $parametre->cree_le
     * Exemple     : "18/06/2026 à 14h30"
     */
    public function getCreeLeAttribute(): string
    {
        if (!$this->created_at) return '—';

        return Carbon::parse($this->created_at)->format('d/m/Y à H\hi');
    }

    /**
     * Date de dernière modification formatée en français.
     *
     * Utilisation : $parametre->modifie_le
     */
    public function getModifieLeAttribute(): string
    {
        if (!$this->updated_at) return '—';

        return Carbon::parse($this->updated_at)->format('d/m/Y à H\hi');
    }
}