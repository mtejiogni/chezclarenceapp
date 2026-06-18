<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametres', function (Blueprint $table) {

            // ── Clé primaire ─────────────────────────────────────
            $table->id('idparametres');

            // ── SECTION : Identité ────────────────────────────────
            $table->string('entreprise', 128)->nullable();
            $table->string('nom_restaurant', 128);
            $table->string('slogan', 200)->nullable();
            $table->text('description')->nullable();
            $table->text('logo')->nullable();

            // ── SECTION : Coordonnées ─────────────────────────────
            $table->text('adresse')->nullable();
            $table->string('latitude', 128)->nullable();
            $table->string('longitude', 128)->nullable();
            $table->string('telephone', 128)->nullable();
            $table->string('telephone2', 20)->nullable();
            $table->string('email', 128)->nullable();
            $table->string('ville', 100)->default('Douala');
            $table->string('horaires', 200)->nullable();

            // ── SECTION : WhatsApp ────────────────────────────────
            $table->string('whatsapp', 20)->nullable();
            $table->text('message_whatsapp')->nullable();

            // ── SECTION : Caisse & reçus ──────────────────────────
            $table->string('devise', 10)->default('FCFA');
            $table->float('tva')->default(0);
            $table->string('prefixe_recu', 5)->default('CC');
            $table->string('pied_recu', 300)->nullable();
            $table->string('mention_legale', 200)->nullable();

            // ── Timestamps ───────────────────────────────────────
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametres');
    }
};