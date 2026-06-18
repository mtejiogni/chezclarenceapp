<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParametreSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('parametres')->insert([

            // ── Identité ─────────────────────────────────────────
            'entreprise'     => 'Chez Clarence',
            'nom_restaurant' => 'Chez Clarence',
            'slogan'         => 'Le goût de chez nous, servi avec le cœur',
            'description'    => 'Restaurant de cuisine camerounaise authentique à Douala. '
                              . 'Spécialités locales, grillades et boissons fraîches.',
            'logo'           => null,
            // Le logo sera uploadé depuis l'interface admin (Paramètres → Identité)

            // ── Coordonnées ───────────────────────────────────────
            'adresse'    => 'Akwa, Douala, Cameroun',
            'latitude'   => '4.0511',
            'longitude'  => '9.7679',
            'telephone'  => '+237697999111',
            'telephone2' => null,
            'email'      => 'contact@chezclarence.cm',
            'ville'      => 'Douala',
            'horaires'   => 'Lun–Sam : 7h–23h · Dim : 9h–21h',

            // ── WhatsApp ──────────────────────────────────────────
            // Format international sans espaces ni + pour les liens wa.me
            // Le contrôleur nettoie automatiquement le numéro à la sauvegarde
            'whatsapp'         => '+237697999111',
            'message_whatsapp' => 'Bonjour Chez Clarence ! Je souhaite passer une commande.',

            // ── Caisse & reçus ────────────────────────────────────
            'devise'         => 'FCFA',
            'tva'            => 0,       // 0 = TVA non applicable
            'prefixe_recu'   => 'CC',    // ex : CC-2026-00001
            'pied_recu'      => 'Merci pour votre visite ! À bientôt chez Clarence.',
            'mention_legale' => null,    // ex : RC/DLA/2018/B/1234

            // ── Timestamps ───────────────────────────────────────
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}