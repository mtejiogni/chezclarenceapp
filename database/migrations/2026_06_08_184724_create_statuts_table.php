<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statuts', function (Blueprint $table) {
            $table->id('idstatut');
            $table->string('intitule', 128)->nullable();
            // Valeurs : En attente, En préparation, Expédiée, Livrée, Servie, Annulée
            $table->text('description')->nullable();
            $table->integer('priorite')->nullable();
            // Priorité : définit l'ordre d'affichage (1 = premier)
            $table->string('void', 128)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statuts');
    }
};
