<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hacer paciente_id y profesional_id nullable manteniendo UUID
        Schema::table('matching_logs', function (Blueprint $table) {
            $table->uuid('paciente_id')->nullable()->change();
            $table->uuid('profesional_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('matching_logs', function (Blueprint $table) {
            $table->uuid('paciente_id')->nullable(false)->change();
            $table->uuid('profesional_id')->nullable(false)->change();
        });
    }
};
