<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id('idcategorie');
            $table->string('intitule', 128)->nullable();
            // Exemples : Grillades, Boissons, Vins, Entrées, Desserts
            $table->text('description')->nullable();
            $table->text('photo')->nullable();
            $table->string('statut', 128)->nullable()->default('Activé');
            // Valeurs : Activé, Désactivé
            $table->string('void', 128)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
