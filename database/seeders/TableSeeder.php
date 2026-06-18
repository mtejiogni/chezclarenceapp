<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TableResto;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        $tables = [
            ['intitule' => 'Table 01', 'description' => 'Salle principale - 4 personnes', 'void' => null],
            ['intitule' => 'Table 02', 'description' => 'Salle principale - 4 personnes', 'void' => null],
            ['intitule' => 'Table 03', 'description' => 'Salle principale - 4 personnes', 'void' => null],
            ['intitule' => 'Table 04', 'description' => 'Salle principale - 4 personnes', 'void' => null],
            ['intitule' => 'Table 05', 'description' => 'Salle principale - 6 personnes', 'void' => null],
            ['intitule' => 'Table 06', 'description' => 'Salle principale - 6 personnes', 'void' => null],
            ['intitule' => 'Table 07', 'description' => 'Salle principale - 6 personnes', 'void' => null],
            ['intitule' => 'Table 08', 'description' => 'Salle principale - 6 personnes', 'void' => null],
            ['intitule' => 'Table VIP 01', 'description' => 'Salon VIP - 8 personnes', 'void' => null],
            ['intitule' => 'Table VIP 02', 'description' => 'Salon VIP - 8 personnes', 'void' => null],
            ['intitule' => 'Terrasse 01', 'description' => 'Terrasse extérieure - 4 personnes', 'void' => null],
            ['intitule' => 'Terrasse 02', 'description' => 'Terrasse extérieure - 4 personnes', 'void' => null],
            ['intitule' => 'Terrasse 03', 'description' => 'Terrasse extérieure - 4 personnes', 'void' => null],
            ['intitule' => 'Bar 01',      'description' => 'Comptoir bar - 2 personnes',    'void' => null],
            ['intitule' => 'Bar 02',      'description' => 'Comptoir bar - 2 personnes',    'void' => null],
        ];

        foreach ($tables as $table) {
            TableResto::create($table);
        }
    }
}