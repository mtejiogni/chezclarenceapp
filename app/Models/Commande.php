<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commande extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'idcommande';

    protected $fillable = [
        'idclient',
        'iduser',
        'idtable',
        'typecommande',
        'reference',
        'montant',
        'adresse',
        'latitude',
        'longitude',
        'consignes',
        'mode_paiement',
        'heurecommande',
        'datecommande',
        'statut_courant',
        'note',
        'commentaires',
        'void',
    ];

    protected $casts = [
        'montant'      => 'decimal:2',
        'datecommande' => 'date',
        'note'         => 'integer',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    public function client()
    {
        return $this->belongsTo(User::class, 'idclient', 'iduser');
    }

    public function serveur()
    {
        return $this->belongsTo(User::class, 'iduser', 'iduser');
    }

    public function table()
    {
        return $this->belongsTo(TableResto::class, 'idtable', 'idtable');
    }

    public function lignes()
    {
        return $this->hasMany(Ligne::class, 'idcommande', 'idcommande');
    }

    public function historiques()
    {
        return $this->hasMany(Historique::class, 'idcommande', 'idcommande')
                    ->orderBy('created_at');
    }

    // =========================================================
    // SCOPES
    // =========================================================

    // Commande::duJour()->get()
    public function scopeDuJour($query)
    {
        return $query->whereDate('datecommande', today());
    }

    // Commande::standard()->get()
    public function scopeStandard($query)
    {
        return $query->where('typecommande', 'Standard');
    }

    // Commande::livraison()->get()
    public function scopeLivraison($query)
    {
        return $query->where('typecommande', 'Livraison');
    }

    // Commande::actives()->get()
    public function scopeActives($query)
    {
        return $query->whereNotIn('statut_courant', ['Servie', 'Livrée', 'Annulée'])
                     ->whereNull('void');
    }

    // Commande::terminees()->get()
    public function scopeTerminees($query)
    {
        return $query->whereIn('statut_courant', ['Servie', 'Livrée'])
                     ->whereNull('void');
    }

    // Commande::parPeriode('2026-06-01', '2026-06-30')->get()
    public function scopeParPeriode($query, string $debut, string $fin)
    {
        return $query->whereBetween('datecommande', [$debut, $fin]);
    }

    // =========================================================
    // ACCESSEURS
    // =========================================================

    // Montant formaté : "3 500 FCFA"
    public function getMontantFormatteAttribute(): string
    {
        return number_format($this->montant, 0, ',', ' ') . ' FCFA';
    }

    // Couleur du badge selon le statut courant
    public function getCouleurStatutAttribute(): string
    {
        return match($this->statut_courant) {
            'En attente'     => 'yellow',
            'En préparation' => 'blue',
            'Expédiée'       => 'orange',
            'Livrée'         => 'green',
            'Servie'         => 'green',
            'Annulée'        => 'red',
            default          => 'gray',
        };
    }

    // Icône Font Awesome selon le statut
    public function getIconeStatutAttribute(): string
    {
        return match($this->statut_courant) {
            'En attente'     => 'fa-clock',
            'En préparation' => 'fa-fire-burner',
            'Expédiée'       => 'fa-motorcycle',
            'Livrée'         => 'fa-circle-check',
            'Servie'         => 'fa-utensils',
            'Annulée'        => 'fa-circle-xmark',
            default          => 'fa-circle',
        };
    }

    // Vérifier si la commande est modifiable
    public function estModifiable(): bool
    {
        return in_array($this->statut_courant, ['En attente', 'En préparation']);
    }

    // Vérifier si la commande est annulable
    public function estAnnulable(): bool
    {
        return !in_array($this->statut_courant, ['Livrée', 'Servie', 'Annulée']);
    }

    // =========================================================
    // METHODES STATIQUES
    // =========================================================

    // Générer une référence unique sans collision
    public static function genererReference(): string
    {
        do {
            $reference = 'CMD-' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('reference', $reference)->exists());

        return $reference;
    }

    // Chiffre d'affaires du jour
    public static function caJour(): float
    {
        return self::duJour()
                   ->terminees()
                   ->sum('montant') ?? 0;
    }

    // Chiffre d'affaires du mois
    public static function caMois(): float
    {
        return self::whereMonth('datecommande', now()->month)
                   ->whereYear('datecommande', now()->year)
                   ->terminees()
                   ->sum('montant') ?? 0;
    }
}