<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id('idmenu');

            $table->unsignedBigInteger('idcategorie');
            // Obligatoire : chaque plat appartient à une catégorie

            $table->string('intitule', 128)->nullable();
            // Exemples : Poulet DG, Ndolé, Poisson Braisé

            $table->text('description')->nullable();
            $table->decimal('pu', 10, 2)->nullable();
            // pu = prix unitaire en FCFA

            $table->text('photo')->nullable();
            // Chemin vers l'image stockée dans storage/app/public/menus/

            $table->string('statut', 128)->nullable()->default('Activé');
            // Valeurs : Activé, Désactivé
            // Désactivé = le plat n'apparaît plus sur l'écran des serveurs

            $table->string('void', 128)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('idcategorie')
                  ->references('idcategorie')
                  ->on('categories')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropForeign(['idcategorie']);
        });
        Schema::dropIfExists('menus');
    }
};