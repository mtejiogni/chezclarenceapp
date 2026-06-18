<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // ── ADMINISTRATEUR ──────────────────────────────────────────
            [
                'nom'       => 'ADMIN',
                'prenom'    => 'Super',
                'sexe'      => 'Masculin',
                'telephone' => '697000001',
                'email'     => 'admin@chezclarence.cm',
                'password'  => Hash::make('Admin@2026!'),
                'role'      => 'Administrateur',
                'statut'    => 'Activé',
                'etat'      => 'Déconnecté',
                'points'    => 0,
                'void'      => null,
            ],

            // ── CAISSIER ─────────────────────────────────────────────────
            [
                'nom'       => 'CAISSE',
                'prenom'    => 'Marie',
                'sexe'      => 'Féminin',
                'telephone' => '697000002',
                'email'     => 'caissier@chezclarence.cm',
                'password'  => Hash::make('Caissier@2026!'),
                'role'      => 'Caissier',
                'statut'    => 'Activé',
                'etat'      => 'Déconnecté',
                'points'    => 0,
                'void'      => null,
            ],

            // ── SERVEUR ──────────────────────────────────────────────────
            [
                'nom'       => 'SERVEUR',
                'prenom'    => 'Paul',
                'sexe'      => 'Masculin',
                'telephone' => '697000003',
                'email'     => 'serveur@chezclarence.cm',
                'password'  => Hash::make('Serveur@2026!'),
                'role'      => 'Serveur',
                'statut'    => 'Activé',
                'etat'      => 'Déconnecté',
                'points'    => 0,
                'void'      => null,
            ],

            // ── CUISINIER ────────────────────────────────────────────────
            [
                'nom'       => 'CUISINE',
                'prenom'    => 'Pierre',
                'sexe'      => 'Masculin',
                'telephone' => '697000004',
                'email'     => 'cuisinier@chezclarence.cm',
                'password'  => Hash::make('Cuisinier@2026!'),
                'role'      => 'Cuisinier',
                'statut'    => 'Activé',
                'etat'      => 'Déconnecté',
                'points'    => 0,
                'void'      => null,
            ],

            // ── LIVREUR ──────────────────────────────────────────────────
            [
                'nom'       => 'LIVRAISON',
                'prenom'    => 'Jacques',
                'sexe'      => 'Masculin',
                'telephone' => '697000005',
                'email'     => 'livreur@chezclarence.cm',
                'password'  => Hash::make('Livreur@2026!'),
                'role'      => 'Livreur',
                'statut'    => 'Activé',
                'etat'      => 'Déconnecté',
                'points'    => 0,
                'void'      => null,
            ],

            // ── CLIENT TEST ──────────────────────────────────────────────
            [
                'nom'        => 'CLIENT',
                'prenom'     => 'Test',
                'sexe'       => 'Masculin',
                'telephone'  => '697000006',
                'email'      => 'client@chezclarence.cm',
                'password'   => Hash::make('Client@2026!'),
                'role'       => 'Client',
                'statut'     => 'Activé',
                'etat'       => 'Déconnecté',
                'points'     => 0,
                'preferences'=> 'Sans piment',
                'void'       => null,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}