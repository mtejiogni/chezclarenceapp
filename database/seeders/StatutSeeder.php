<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Statut;

class StatutSeeder extends Seeder
{
    public function run(): void
    {
        $statuts = [
            [
                'intitule'    => 'En attente',
                'description' => 'Commande enregistrée mais pas encore prise en charge par la cuisine.',
                'priorite'    => 1,
                'void'        => null,
            ],
            [
                'intitule'    => 'En préparation',
                'description' => 'La cuisine travaille activement sur cette commande.',
                'priorite'    => 2,
                'void'        => null,
            ],
            [
                'intitule'    => 'Expédiée',
                'description' => 'La commande a quitté le restaurant avec le livreur.',
                'priorite'    => 3,
                'void'        => null,
            ],
            [
                'intitule'    => 'Livrée',
                'description' => 'Le livreur est revenu avec le paiement. Commande clôturée.',
                'priorite'    => 4,
                'void'        => null,
            ],
            [
                'intitule'    => 'Servie',
                'description' => 'Commande servie à la table. Consommation terminée.',
                'priorite'    => 4,
                'void'        => null,
            ],
            [
                'intitule'    => 'Annulée',
                'description' => 'Commande annulée. Nécessite une justification obligatoire.',
                'priorite'    => 5,
                'void'        => null,
            ],
        ];

        foreach ($statuts as $statut) {
            Statut::create($statut);
        }
    }
}