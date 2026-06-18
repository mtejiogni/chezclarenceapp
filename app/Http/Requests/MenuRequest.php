<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Seuls Admin et Caissier peuvent créer/modifier des plats
        return in_array(auth()->user()->role, ['Administrateur', 'Caissier']);
    }

    public function rules(): array
    {
        // En modification, on exclut l'ID du menu courant si nécessaire
        $menuId = $this->route('menu')?->idmenu;

        return [
            'idcategorie' => 'required|exists:categories,idcategorie',
            'intitule'    => 'required|string|max:128',
            'description' => 'nullable|string|max:1000',
            'pu'          => 'required|numeric|min:1|max:9999999',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'statut'      => 'required|in:Activé,Désactivé',
        ];
    }

    public function messages(): array
    {
        return [
            'idcategorie.required' => 'Veuillez sélectionner une catégorie.',
            'idcategorie.exists'   => 'Cette catégorie n\'existe pas.',
            'intitule.required'    => 'Le nom du plat est obligatoire.',
            'intitule.max'         => 'Le nom ne peut pas dépasser 128 caractères.',
            'pu.required'          => 'Le prix unitaire est obligatoire.',
            'pu.numeric'           => 'Le prix doit être un nombre.',
            'pu.min'               => 'Le prix doit être supérieur à 0.',
            'pu.max'               => 'Le prix semble trop élevé.',
            'photo.image'          => 'Le fichier doit être une image.',
            'photo.mimes'          => 'Formats acceptés : jpeg, png, jpg, webp.',
            'photo.max'            => 'La photo ne doit pas dépasser 2 Mo.',
            'statut.required'      => 'Le statut est obligatoire.',
            'statut.in'            => 'Le statut doit être Activé ou Désactivé.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Nettoyer le nom du plat
        if ($this->filled('intitule')) {
            $this->merge([
                'intitule' => ucfirst(trim($this->intitule)),
            ]);
        }

        // S'assurer que le prix est bien un nombre
        if ($this->filled('pu')) {
            $this->merge([
                'pu' => (float) str_replace([' ', ','], ['', '.'], $this->pu),
            ]);
        }
    }
}