<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statut extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'idstatut';

    protected $fillable = [
        'intitule',
        'description',
        'priorite',
        'void',
    ];

    protected $casts = [
        'priorite' => 'integer',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    public function historiques()
    {
        return $this->hasMany(Historique::class, 'idstatut', 'idstatut');
    }

    // =========================================================
    // SCOPES
    // =========================================================

    // Statut::ordonnes()->get()
    public function scopeOrdonnes($query)
    {
        return $query->orderBy('priorite')->whereNull('void');
    }

    // =========================================================
    // METHODES STATIQUES
    // =========================================================

    // Récupérer un statut par son intitulé exact
    // Utilisation : Statut::parIntitule('En attente')
    public static function parIntitule(string $intitule): ?self
    {
        return self::where('intitule', $intitule)->first();
    }

    // =========================================================
    // ACCESSEURS
    // =========================================================

    // Couleur CSS associée à chaque statut pour les badges
    public function getCouleurAttribute(): string
    {
        return match($this->intitule) {
            'En attente'     => 'yellow',
            'En préparation' => 'blue',
            'Expédiée'       => 'orange',
            'Livrée'         => 'green',
            'Servie'         => 'green',
            'Annulée'        => 'red',
            default          => 'gray',
        };
    }

    // Icône Font Awesome associée
    public function getIconeAttribute(): string
    {
        return match($this->intitule) {
            'En attente'     => 'fa-clock',
            'En préparation' => 'fa-fire-burner',
            'Expédiée'       => 'fa-motorcycle',
            'Livrée'         => 'fa-circle-check',
            'Servie'         => 'fa-utensils',
            'Annulée'        => 'fa-circle-xmark',
            default          => 'fa-circle',
        };
    }
}