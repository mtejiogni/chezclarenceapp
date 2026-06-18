<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Categorie;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer les IDs des catégories par leur intitulé
        $grillades    = Categorie::where('intitule', 'Grillades')->first()->idcategorie;
        $platsLocaux  = Categorie::where('intitule', 'Plats locaux')->first()->idcategorie;
        $entrees      = Categorie::where('intitule', 'Entrées')->first()->idcategorie;
        $boissons     = Categorie::where('intitule', 'Boissons locales')->first()->idcategorie;
        $bieres       = Categorie::where('intitule', 'Bières & Softs')->first()->idcategorie;
        $vins         = Categorie::where('intitule', 'Vins')->first()->idcategorie;
        $desserts     = Categorie::where('intitule', 'Desserts')->first()->idcategorie;

        $menus = [

            // ── GRILLADES ────────────────────────────────────────────────
            [
                'idcategorie' => $grillades,
                'intitule'    => 'Poulet Braisé',
                'description' => 'Poulet entier ou demi, mariné et grillé au charbon. Servi avec du plantain ou du bâton de manioc.',
                'pu'          => 3500,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $grillades,
                'intitule'    => 'Poisson Braisé',
                'description' => 'Poisson frais du jour grillé, accompagné de miondo et de sauce tomate pimentée.',
                'pu'          => 4000,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $grillades,
                'intitule'    => 'Bœuf Braisé',
                'description' => 'Morceaux de bœuf marinés, grillés à point. Servi avec du plantain frit.',
                'pu'          => 4500,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $grillades,
                'intitule'    => 'Saucisse Grillée',
                'description' => 'Saucisses artisanales grillées, servies avec moutarde et pain.',
                'pu'          => 2000,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $grillades,
                'intitule'    => 'Côtes de Porc',
                'description' => 'Côtes de porc marinées aux épices locales, grillées au charbon.',
                'pu'          => 4000,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],

            // ── PLATS LOCAUX ─────────────────────────────────────────────
            [
                'idcategorie' => $platsLocaux,
                'intitule'    => 'Ndolé',
                'description' => 'Plat national camerounais à base de feuilles amères, cacahuètes et crevettes. Servi avec du plantain ou du riz.',
                'pu'          => 3000,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $platsLocaux,
                'intitule'    => 'Poulet DG',
                'description' => 'Poulet sauté avec plantains mûrs, légumes et épices. Le plat festif par excellence.',
                'pu'          => 4500,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $platsLocaux,
                'intitule'    => 'Eru',
                'description' => 'Feuilles d\'eru mijotées avec waterleaf, huile de palme et viande fumée.',
                'pu'          => 2500,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $platsLocaux,
                'intitule'    => 'Koki',
                'description' => 'Gâteau de haricots à l\'huile de palme, cuit à la vapeur dans des feuilles de bananier.',
                'pu'          => 1500,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $platsLocaux,
                'intitule'    => 'Okok (Melon)',
                'description' => 'Feuilles d\'okok préparées avec des graines de courge et de la viande de bœuf.',
                'pu'          => 2500,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $platsLocaux,
                'intitule'    => 'Riz Sauté',
                'description' => 'Riz sauté aux légumes, œufs et poulet. Rapide et savoureux.',
                'pu'          => 2000,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],

            // ── ENTRÉES ──────────────────────────────────────────────────
            [
                'idcategorie' => $entrees,
                'intitule'    => 'Salade Verte',
                'description' => 'Salade fraîche avec tomates, carottes, concombre et vinaigrette maison.',
                'pu'          => 1000,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $entrees,
                'intitule'    => 'Soupe de Poisson',
                'description' => 'Soupe légère au poisson frais, épices et légumes du marché.',
                'pu'          => 1500,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $entrees,
                'intitule'    => 'Beignets Haricots',
                'description' => 'Beignets de haricots croustillants, servis avec poivron et piment.',
                'pu'          => 500,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],

            // ── BOISSONS LOCALES ─────────────────────────────────────────
            [
                'idcategorie' => $boissons,
                'intitule'    => 'Jus de Gingembre',
                'description' => 'Jus de gingembre frais, légèrement sucré et épicé. Servi frais.',
                'pu'          => 500,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $boissons,
                'intitule'    => 'Bissap (Hibiscus)',
                'description' => 'Boisson naturelle à base de fleurs d\'hibiscus. Rafraîchissante et vitaminée.',
                'pu'          => 500,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $boissons,
                'intitule'    => 'Foléré',
                'description' => 'Boisson traditionnelle du nord Cameroun à base de calice d\'oseille.',
                'pu'          => 500,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $boissons,
                'intitule'    => 'Jus d\'Ananas',
                'description' => 'Jus d\'ananas frais pressé, sans sucre ajouté.',
                'pu'          => 600,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $boissons,
                'intitule'    => 'Eau de Coco',
                'description' => 'Eau de coco naturelle, directement depuis la noix.',
                'pu'          => 500,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],

            // ── BIÈRES & SOFTS ───────────────────────────────────────────
            [
                'idcategorie' => $bieres,
                'intitule'    => 'Castel Bière',
                'description' => 'Bière locale camerounaise, bouteille 65cl, servie fraîche.',
                'pu'          => 800,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $bieres,
                'intitule'    => '33 Export',
                'description' => 'Bière blonde légère, bouteille 65cl, servie fraîche.',
                'pu'          => 800,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $bieres,
                'intitule'    => 'Coca-Cola',
                'description' => 'Soda Coca-Cola, bouteille 60cl, servi frais.',
                'pu'          => 500,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $bieres,
                'intitule'    => 'Eau Minérale',
                'description' => 'Eau minérale naturelle, bouteille 1,5L.',
                'pu'          => 300,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $bieres,
                'intitule'    => 'Malta Guinness',
                'description' => 'Boisson maltée sans alcool, riche en vitamines. Bouteille 65cl.',
                'pu'          => 600,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],

            // ── VINS ─────────────────────────────────────────────────────
            [
                'idcategorie' => $vins,
                'intitule'    => 'Vin Rouge (verre)',
                'description' => 'Verre de vin rouge de table, 15cl.',
                'pu'          => 1500,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $vins,
                'intitule'    => 'Vin Blanc (verre)',
                'description' => 'Verre de vin blanc sec ou demi-sec, 15cl.',
                'pu'          => 1500,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $vins,
                'intitule'    => 'Vin Rosé (verre)',
                'description' => 'Verre de vin rosé frais, 15cl.',
                'pu'          => 1500,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],

            // ── DESSERTS ─────────────────────────────────────────────────
            [
                'idcategorie' => $desserts,
                'intitule'    => 'Salade de Fruits',
                'description' => 'Mélange de fruits tropicaux frais de saison : mangue, papaye, ananas, banane.',
                'pu'          => 1000,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $desserts,
                'intitule'    => 'Gâteau Chocolat',
                'description' => 'Part de gâteau au chocolat fondant, fait maison.',
                'pu'          => 1500,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
            [
                'idcategorie' => $desserts,
                'intitule'    => 'Beignets Sucrés',
                'description' => 'Beignets moelleux saupoudrés de sucre glace, servis chauds.',
                'pu'          => 500,
                'photo'       => null,
                'statut'      => 'Activé',
                'void'        => null,
            ],
        ];

        foreach ($menus as $menu) {
            Menu::create($menu);
        }
    }
}