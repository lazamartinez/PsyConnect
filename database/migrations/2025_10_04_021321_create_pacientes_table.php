<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            // UUID como primary key con generación automática
            $table->uuid('id_paciente')->primary()->default(DB::raw('gen_random_uuid()'));
            
            $table->foreignId('usuario_id')
                  ->constrained('usuarios', 'id_usuario')
                  ->onDelete('cascade'); // opcional: elimina paciente si usuario se borra
            
            $table->date('fecha_nacimiento');
            $table->enum('genero', ['masculino', 'femenino', 'otro', 'prefiero_no_decir']);
            
            $table->text('direccion')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('contacto_emergencia_nombre')->nullable();
            $table->string('contacto_emergencia_telefono')->nullable();
            $table->text('alergias')->nullable();
            $table->text('medicamentos_actuales')->nullable();
            
            $table->enum('estado_tratamiento', ['activo', 'inactivo', 'alta'])->default('activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
