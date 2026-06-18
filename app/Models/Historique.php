<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Historique extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'idhistorique';

    protected $fillable = [
        'idcommande',
        'idstatut',
        'description',
        'void',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    public function commande()
    {
        return $this->belongsTo(Commande::class, 'idcommande', 'idcommande');
    }

    public function statut()
    {
        return $this->belongsTo(Statut::class, 'idstatut', 'idstatut');
    }

    // =========================================================
    // METHODE STATIQUE
    // =========================================================

    // Enregistrer un changement de statut facilement
    // Utilisation : Historique::enregistrer($commande, 'En préparation', 'Pris en charge par Paul')
    public static function enregistrer(
        Commande $commande,
        string $intituleStatut,
        string $description = ''
    ): self {
        $statut = Statut::parIntitule($intituleStatut);

        // Mettre à jour le statut courant de la commande
        $commande->update(['statut_courant' => $intituleStatut]);

        // Créer l'entrée dans l'historique
        return self::create([
            'idcommande'  => $commande->idcommande,
            'idstatut'    => $statut->idstatut,
            'description' => $description ?: 'Statut changé en : ' . $intituleStatut,
        ]);
    }

    // =========================================================
    // ACCESSEURS
    // =========================================================

    // Date et heure formatées
    public function getDateFormatteAttribute(): string
    {
        return $this->created_at
            ? $this->created_at->format('d/m/Y à H:i')
            : '';
    }
}