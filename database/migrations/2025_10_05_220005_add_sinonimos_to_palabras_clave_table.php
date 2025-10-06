<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('palabras_clave', function (Blueprint $table) {
            $table->json('sinonimos')->nullable()->after('especialidad_recomendada');
        });
    }

    public function down(): void
    {
        Schema::table('palabras_clave', function (Blueprint $table) {
            $table->dropColumn('sinonimos');
        });
    }
};
