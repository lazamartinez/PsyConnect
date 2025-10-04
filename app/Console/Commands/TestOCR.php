<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OCRService;

class TestOCR extends Command
{
    protected $signature = 'psiconnect:test-ocr {image?}';
    protected $description = 'Probar el sistema OCR con una imagen';

    public function handle()
    {
        $ocrService = new OCRService();
        
        $image = $this->argument('image') ?: storage_path('app/public/manuscritos/ejemplo.jpg');
        
        if (!file_exists($image)) {
            $this->error("La imagen no existe: {$image}");
            $this->info("Puedes subir una imagen a: storage/app/public/manuscritos/");
            return;
        }

        $this->info("Procesando imagen: {$image}");
        
        $result = $ocrService->procesarImagen($image);
        
        $this->info("✅ OCR Completado:");
        $this->line("Confianza: {$result['confianza']}%");
        $this->line("Longitud texto: {$result['longitud_texto']} caracteres");
        $this->line("Procesado con: {$result['procesado_con']}");
        $this->line("\n📝 Texto extraído:");
        $this->line($result['texto']);
    }
}