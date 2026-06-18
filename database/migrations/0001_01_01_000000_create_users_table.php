<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('iduser');
            $table->string('nom', 128)->nullable();
            $table->string('prenom', 128)->nullable();
            $table->string('sexe', 128)->nullable();
            $table->text('adresse')->nullable();
            $table->string('latitude', 128)->nullable();
            $table->string('longitude', 128)->nullable();
            $table->string('telephone', 128)->nullable();
            $table->string('email', 128)->unique()->nullable();
            $table->string('password')->nullable();
            $table->text('preferences')->nullable();
            $table->integer('points')->nullable()->default(0);
            $table->string('role', 128)->nullable()->default('Client');
            // Rôles : Client, Serveur, Livreur, Cuisinier, Caissier, Administrateur
            $table->string('etat', 128)->nullable()->default('Déconnecté');
            $table->string('statut', 128)->nullable()->default('Activé');
            $table->text('photo')->nullable();
            $table->string('void', 128)->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};