<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ligne extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'idligne';

    protected $fillable = [
        'idcommande',
        'idmenu',
        'quantite',
        'remise',
        'prix',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'remise'   => 'decimal:2',
        'prix'     => 'decimal:2',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    public function commande()
    {
        return $this->belongsTo(Commande::class, 'idcommande', 'idcommande');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'idmenu', 'idmenu');
    }

    // =========================================================
    // ACCESSEURS
    // =========================================================

    // Prix formaté
    public function getPrixFormatteAttribute(): string
    {
        return number_format($this->prix, 0, ',', ' ') . ' FCFA';
    }

    // Prix unitaire calculé (prix / quantite)
    public function getPrixUnitaireAttribute(): float
    {
        return $this->quantite > 0
            ? round($this->prix / $this->quantite, 2)
            : 0;
    }

    // =========================================================
    // MUTATEURS
    // =========================================================

    // Calculer automatiquement le prix total à la création
    public static function calculerPrix(float $pu, int $quantite, float $remise = 0): float
    {
        return max(0, ($pu * $quantite) - $remise);
    }
}