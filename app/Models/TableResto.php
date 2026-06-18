<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TableResto extends Model
{
    use SoftDeletes;

    // Nom réel de la table en base de données
    protected $table = 'tables';

    protected $primaryKey = 'idtable';

    protected $fillable = [
        'intitule',
        'description',
        'void',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    // Toutes les commandes de cette table
    public function commandes()
    {
        return $this->hasMany(Commande::class, 'idtable', 'idtable');
    }

    // Commandes actives (non terminées)
    public function commandesActives()
    {
        return $this->hasMany(Commande::class, 'idtable', 'idtable')
                    ->whereNotIn('statut_courant', ['Servie', 'Livrée', 'Annulée'])
                    ->whereNull('void');
    }

    // =========================================================
    // ACCESSEURS
    // =========================================================

    // Vérifier si la table est actuellement occupée
    public function getEstOccupeeAttribute(): bool
    {
        return $this->commandesActives()->exists();
    }

    // Classe CSS de couleur selon disponibilité
    public function getCouleurStatutAttribute(): string
    {
        return $this->est_occupee ? 'red' : 'green';
    }

    // Texte du statut
    public function getTexteStatutAttribute(): string
    {
        return $this->est_occupee ? 'Occupée' : 'Libre';
    }

    // =========================================================
    // SCOPES
    // =========================================================

    // TableResto::disponibles()->get()
    public function scopeDisponibles($query)
    {
        return $query->whereNull('void')
                     ->whereDoesntHave('commandesActives');
    }

    // TableResto::occupees()->get()
    public function scopeOccupees($query)
    {
        return $query->whereNull('void')
                     ->whereHas('commandesActives');
    }
}