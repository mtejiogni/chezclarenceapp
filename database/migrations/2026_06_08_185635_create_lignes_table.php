<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lignes', function (Blueprint $table) {
            $table->id('idligne');

            $table->unsignedBigInteger('idcommande');
            // La commande à laquelle appartient cette ligne

            $table->unsignedBigInteger('idmenu');
            // Le plat commandé

            $table->integer('quantite')->nullable()->default(1);
            // Nombre d'exemplaires commandés

            $table->decimal('remise', 10, 2)->nullable()->default(0);
            // Remise appliquée sur cette ligne (en FCFA)

            $table->decimal('prix', 10, 2)->nullable();
            // Prix total de la ligne = (pu * quantite) - remise

            // Pas de timestamps ni softDeletes sur les lignes :
            // une ligne supprimée = commande annulée (géré au niveau commande)

            $table->foreign('idcommande')
                  ->references('idcommande')
                  ->on('commandes')
                  ->onDelete('cascade');
            // Si la commande est supprimée, ses lignes le sont aussi

            $table->foreign('idmenu')
                  ->references('idmenu')
                  ->on('menus')
                  ->onDelete('restrict');
            // On ne peut pas supprimer un plat s'il est dans une commande
        });
    }

    public function down(): void
    {
        Schema::table('lignes', function (Blueprint $table) {
            $table->dropForeign(['idcommande']);
            $table->dropForeign(['idmenu']);
        });
        Schema::dropIfExists('lignes');
    }
};