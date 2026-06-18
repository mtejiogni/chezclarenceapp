<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categorie extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'idcategorie';

    protected $fillable = [
        'intitule',
        'description',
        'photo',
        'statut',
        'void',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    // Tous les plats de la catégorie
    public function menus()
    {
        return $this->hasMany(Menu::class, 'idcategorie', 'idcategorie');
    }

    // Uniquement les plats actifs et non supprimés
    public function menusActifs()
    {
        return $this->hasMany(Menu::class, 'idcategorie', 'idcategorie')
                    ->where('statut', 'Activé')
                    ->whereNull('void');
    }

    // =========================================================
    // SCOPES
    // =========================================================

    // Categorie::actives()->get()
    public function scopeActives($query)
    {
        return $query->where('statut', 'Activé')->whereNull('void');
    }

    // =========================================================
    // ACCESSEURS
    // =========================================================

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return asset('img/categorie-default.png');
    }

    // Nombre de plats actifs dans la catégorie
    public function getNbPlatsAttribute(): int
    {
        return $this->menusActifs()->count();
    }

    // =========================================================
    // MUTATEURS
    // =========================================================

    // Mettre automatiquement la première lettre en majuscule
    public function setIntituleAttribute(string $value): void
    {
        $this->attributes['intitule'] = ucfirst(trim($value));
    }
}