<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historiques', function (Blueprint $table) {
            $table->id('idhistorique');

            $table->unsignedBigInteger('idcommande');
            // La commande concernée par ce changement de statut

            $table->unsignedBigInteger('idstatut');
            // Le nouveau statut appliqué

            $table->text('description')->nullable();
            // Exemple : "Statut changé en Expédiée par Jean (Livreur)"

            $table->string('void', 128)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('idcommande')
                  ->references('idcommande')
                  ->on('commandes')
                  ->onDelete('cascade');

            $table->foreign('idstatut')
                  ->references('idstatut')
                  ->on('statuts')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('historiques', function (Blueprint $table) {
            $table->dropForeign(['idcommande']);
            $table->dropForeign(['idstatut']);
        });
        Schema::dropIfExists('historiques');
    }
};