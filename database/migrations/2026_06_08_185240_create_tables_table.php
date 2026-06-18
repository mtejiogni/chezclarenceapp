<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Note : on utilise le nom 'tables' en base
        // mais Laravel réserve ce mot donc on l'échappe avec Schema::create
        Schema::create('tables', function (Blueprint $table) {
            $table->id('idtable');
            $table->string('intitule', 128)->nullable();
            // Exemples : Table 1, Table VIP, Terrasse 3
            $table->text('description')->nullable();
            $table->string('void', 128)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};