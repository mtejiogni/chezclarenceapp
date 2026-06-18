<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'idmenu';

    protected $fillable = [
        'idcategorie',
        'intitule',
        'description',
        'pu',
        'photo',
        'statut',
        'void',
    ];

    protected $casts = [
        'pu' => 'decimal:2',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    // Le plat appartient à une catégorie
    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'idcategorie', 'idcategorie');
    }

    // Le plat peut apparaître dans plusieurs lignes de commande
    public function lignes()
    {
        return $this->hasMany(Ligne::class, 'idmenu', 'idmenu');
    }

    // =========================================================
    // SCOPES
    // =========================================================

    // Menu::actifs()->get()
    public function scopeActifs($query)
    {
        return $query->where('statut', 'Activé')->whereNull('void');
    }

    // Menu::parCategorie(2)->get()
    public function scopeParCategorie($query, int $idcategorie)
    {
        return $query->where('idcategorie', $idcategorie);
    }

    // =========================================================
    // ACCESSEURS
    // =========================================================

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return asset('img/food-default.png');
    }

    // Prix formaté en FCFA : "3 500 FCFA"
    public function getPrixFormatteAttribute(): string
    {
        return number_format($this->pu, 0, ',', ' ') . ' FCFA';
    }

    // Nombre de fois que ce plat a été commandé
    public function getNbCommandesAttribute(): int
    {
        return $this->lignes()->sum('quantite') ?? 0;
    }

    // =========================================================
    // MUTATEURS
    // =========================================================

    public function setIntituleAttribute(string $value): void
    {
        $this->attributes['intitule'] = ucfirst(trim($value));
    }

    public function setPuAttribute($value): void
    {
        $this->attributes['pu'] = max(0, (float) $value);
    }
}