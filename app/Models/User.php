<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $primaryKey = 'iduser';

    protected $fillable = [
        'nom',
        'prenom',
        'sexe',
        'adresse',
        'latitude',
        'longitude',
        'telephone',
        'email',
        'password',
        'preferences',
        'points',
        'role',
        'etat',
        'statut',
        'photo',
        'void',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'points'   => 'integer',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    // Commandes passées en tant que client
    public function commandesClient()
    {
        return $this->hasMany(Commande::class, 'idclient', 'iduser');
    }

    // Commandes enregistrées en tant que serveur/caissier
    public function commandesServeur()
    {
        return $this->hasMany(Commande::class, 'iduser', 'iduser');
    }

    // =========================================================
    // HELPERS ROLES
    // =========================================================

    public function isAdmin(): bool
    {
        return $this->role === 'Administrateur';
    }

    public function isCaissier(): bool
    {
        return $this->role === 'Caissier';
    }

    public function isServeur(): bool
    {
        return $this->role === 'Serveur';
    }

    public function isLivreur(): bool
    {
        return $this->role === 'Livreur';
    }

    public function isCuisinier(): bool
    {
        return $this->role === 'Cuisinier';
    }

    public function isClient(): bool
    {
        return $this->role === 'Client';
    }

    // Vérifier si l'utilisateur a un des rôles donnés
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles);
    }

    // =========================================================
    // HELPERS STATUT
    // =========================================================

    public function isActif(): bool
    {
        return $this->statut === 'Activé';
    }

    public function isConnecte(): bool
    {
        return $this->etat === 'Connecté';
    }

    // =========================================================
    // ACCESSEURS
    // =========================================================

    // Nom complet : "DUPONT Jean"
    public function getNomCompletAttribute(): string
    {
        return strtoupper($this->nom) . ' ' . ucfirst($this->prenom);
    }

    // Initiales pour l'avatar : "DJ"
    public function getInitialesAttribute(): string
    {
        return strtoupper(
            substr($this->prenom ?? '', 0, 1) .
            substr($this->nom ?? '', 0, 1)
        );
    }

    // URL complète de la photo de profil
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return asset('img/avatar-default.png');
    }

    // =========================================================
    // SCOPES (filtres réutilisables)
    // =========================================================

    // User::actifs()->get()
    public function scopeActifs($query)
    {
        return $query->where('statut', 'Activé')->whereNull('void');
    }

    // User::parRole('Serveur')->get()
    public function scopeParRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    // User::connectes()->get()
    public function scopeConnectes($query)
    {
        return $query->where('etat', 'Connecté');
    }


    
    //======> API JWT
    // Identifiant placé dans le jeton (la clé primaire de l'utilisateur).
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
 
    // Données additionnelles à embarquer dans le jeton (ici, on en ajoute aucune).
    public function getJWTCustomClaims()
    {
        return [];
    }

}