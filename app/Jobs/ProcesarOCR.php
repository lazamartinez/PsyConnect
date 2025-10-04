<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Manuscrito;
use App\Services\OCRService;

class ProcesarOCR implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $manuscrito;

    public function __construct(Manuscrito $manuscrito)
    {
        $this->manuscrito = $manuscrito;
    }

    public function handle(OCRService $ocrService)
    {
        try {
            $resultado = $ocrService->procesarManuscrito($this->manuscrito);
            
            // Actualizar el manuscrito con los resultados
            $this->manuscrito->update([
                'texto_digitalizado' => $resultado['texto'],
                'confianza_ocr' => $resultado['confianza'],
                'estado_procesamiento' => 'procesado'
            ]);
            
        } catch (\Exception $e) {
            $this->manuscrito->update([
                'estado_procesamiento' => 'error',
                'texto_digitalizado' => 'Error en procesamiento: ' . $e->getMessage()
            ]);
        }
    }
}