<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Eliminar la restricción CHECK existente
        DB::statement('ALTER TABLE manuscritos DROP CONSTRAINT IF EXISTS manuscritos_estado_procesamiento_check');
        
        // Agregar nueva restricción que incluya 'procesando_ocr'
        DB::statement("
            ALTER TABLE manuscritos 
            ADD CONSTRAINT manuscritos_estado_procesamiento_check 
            CHECK (estado_procesamiento IN (
                'pendiente', 
                'procesando', 
                'procesado', 
                'error',
                'procesando_ocr'
            ))
        ");
    }

    public function down()
    {
        DB::statement('ALTER TABLE manuscritos DROP CONSTRAINT IF EXISTS manuscritos_estado_procesamiento_check');
        
        // Restaurar restricción original (sin 'procesando_ocr')
        DB::statement("
            ALTER TABLE manuscritos 
            ADD CONSTRAINT manuscritos_estado_procesamiento_check 
            CHECK (estado_procesamiento IN ('pendiente', 'procesando', 'procesado', 'error'))
        ");
    }
};