<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id('idcommande');

            // Clés étrangères vers users (deux relations distinctes)
            $table->unsignedBigInteger('idclient')->nullable();
            // idclient : le client qui a passé la commande
            $table->unsignedBigInteger('iduser')->nullable();
            // iduser : le serveur/caissier qui a enregistré la commande

            // Clé étrangère vers tables
            $table->unsignedBigInteger('idtable')->nullable();
            // nullable car les livraisons n'ont pas de table

            $table->string('typecommande', 128)->nullable();
            // Valeurs : Standard (en salle), Livraison

            $table->string('reference', 128)->nullable()->unique();
            // Format : CMD-XXXXXX (6 chiffres aléatoires)

            $table->decimal('montant', 10, 2)->nullable()->default(0);

            // Informations de livraison (remplies uniquement si typecommande = Livraison)
            $table->text('adresse')->nullable();
            $table->string('latitude', 128)->nullable();
            $table->string('longitude', 128)->nullable();

            $table->text('consignes')->nullable();
            // Rempli automatiquement avec les préférences du client

            $table->string('mode_paiement', 128)->nullable()->default('Espèces');

            $table->time('heurecommande')->nullable();
            $table->date('datecommande')->nullable();

            $table->string('statut_courant', 128)->nullable()->default('En attente');
            // Valeurs : En attente, En préparation, Expédiée, Livrée, Servie, Annulée

            $table->integer('note')->nullable();
            // Note de 1 à 5 donnée par le client après livraison/service

            $table->text('commentaires')->nullable();
            // Commentaire laissé par le client

            $table->string('void', 128)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Définition des clés étrangères
            $table->foreign('idclient')
                  ->references('iduser')
                  ->on('users')
                  ->onDelete('set null');

            $table->foreign('iduser')
                  ->references('iduser')
                  ->on('users')
                  ->onDelete('set null');

            $table->foreign('idtable')
                  ->references('idtable')
                  ->on('tables')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropForeign(['idclient']);
            $table->dropForeign(['iduser']);
            $table->dropForeign(['idtable']);
        });
        Schema::dropIfExists('commandes');
    }
};