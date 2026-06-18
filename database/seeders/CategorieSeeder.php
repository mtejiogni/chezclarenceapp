<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categorie;

class CategorieSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'intitule'    => 'Grillades',
                'description' => 'Viandes et poissons grillés au charbon de bois.',
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'intitule'    => 'Plats locaux',
                'description' => 'Spécialités camerounaises traditionnelles.',
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'intitule'    => 'Entrées',
                'description' => 'Salades, soupes et amuse-bouches.',
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'intitule'    => 'Boissons locales',
                'description' => 'Jus naturels, bissap, gingembre, foléré.',
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'intitule'    => 'Bières & Softs',
                'description' => 'Bières locales et internationales, sodas, eaux.',
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'intitule'    => 'Vins',
                'description' => 'Vins rouges, blancs et rosés.',
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'intitule'    => 'Desserts',
                'description' => 'Gâteaux, fruits et douceurs maison.',
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
        ];

        foreach ($categories as $categorie) {
            Categorie::create($categorie);
        }
    }
}