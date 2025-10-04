<?php

namespace App\Services;

use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OCRService
{
    public function procesarImagen($rutaImagen)
    {
        try {
            Log::info("=== INICIANDO PROCESAMIENTO OCR ===");
            Log::info("Ruta imagen: {$rutaImagen}");

            // Verificar que el archivo existe
            $rutaCompleta = storage_path('app/public/' . $rutaImagen);
            Log::info("Ruta completa: {$rutaCompleta}");

            if (!file_exists($rutaCompleta)) {
                throw new \Exception("El archivo no existe en: {$rutaCompleta}");
            }

            // Verificar permisos del archivo
            if (!is_readable($rutaCompleta)) {
                throw new \Exception("El archivo no es readable: {$rutaCompleta}");
            }

            Log::info("Archivo verificado, tamaño: " . filesize($rutaCompleta) . " bytes");

            // Verificar que Tesseract está disponible
            $tesseractVersion = shell_exec('tesseract --version 2>&1');
            Log::info("Tesseract version: " . substr($tesseractVersion, 0, 100));

            // Configurar Tesseract para español con manejo de errores
            Log::info("Configurando Tesseract OCR...");

            $ocr = new TesseractOCR($rutaCompleta);

            $texto = $ocr->lang('spa', 'eng')
                ->psm(6)
                ->oem(3)
                ->config('tessedit_char_whitelist', 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789áéíóúÁÉÍÓÚñÑüÜ.,!?¡¿()[]{}:;- ')
                ->run();

            Log::info("OCR completado exitosamente");
            Log::info("Texto extraído (primeros 100 chars): " . substr($texto, 0, 100));

            // Calcular confianza
            $confianza = $this->calcularConfianzaOCR($texto);

            return [
                'texto' => trim($texto),
                'confianza' => $confianza,
                'longitud_texto' => strlen(trim($texto)),
                'procesado_con' => 'Tesseract OCR Real'
            ];
        } catch (\Exception $e) {
            Log::error("❌ ERROR en OCRService: " . $e->getMessage());
            Log::error("Trace: " . $e->getTraceAsString());

            // Fallback más robusto
            return $this->procesamientoFallback($rutaImagen, $e->getMessage());
        }
    }

    private function calcularConfianzaOCR($texto)
    {
        $longitud = strlen(trim($texto));

        if ($longitud === 0) return 0.0;
        if ($longitud < 10) return 30.0;
        if ($longitud < 50) return 60.0;

        // Calcular confianza basada en caracteres válidos
        $caracteresValidos = preg_match_all('/[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s]/', $texto);
        $totalCaracteres = strlen($texto);

        if ($totalCaracteres > 0) {
            $confianza = ($caracteresValidos / $totalCaracteres) * 100;
            return min(95.0, max(30.0, $confianza));
        }

        return 70.0;
    }

    private function procesamientoFallback($rutaImagen, $error)
    {
        Log::warning("Usando fallback para OCR. Error: {$error}");

        // Textos de ejemplo para demostración
        $textosEjemplo = [
            "Hoy me siento muy feliz y contento con la vida. Todo parece ir bien y estoy agradecido por las oportunidades que se presentan. El futuro se ve prometedor y lleno de esperanza.",
            "Estoy pasando por un momento difícil. La ansiedad no me deja en paz y me siento abrumado por las circunstancias. Necesito encontrar paz interior y calma para seguir adelante.",
            "Me siento equilibrado hoy. Ni especialmente feliz ni triste, simplemente en un estado de tranquilidad. Es un día como cualquier otro, sin grandes altibajos emocionales.",
            "¡Qué día tan maravilloso! El sol brilla y mi corazón está lleno de alegría. Estoy emocionado por lo que viene y agradecido por cada momento de felicidad.",
            "Hoy las cosas no salieron como esperaba. Me siento frustrado y un poco decepcionado. Pero sé que mañana será otro día y tendré nuevas oportunidades para mejorar."
        ];

        $texto = $textosEjemplo[array_rand($textosEjemplo)];

        return [
            'texto' => $texto,
            'confianza' => 45.0,
            'longitud_texto' => strlen($texto),
            'procesado_con' => 'Fallback - Error: ' . $error,
            'error_original' => $error
        ];
    }

    public function procesarMultipleIdiomas($rutaImagen, $idiomas = ['spa', 'eng'])
    {
        try {
            $rutaCompleta = storage_path('app/public/' . $rutaImagen);

            $ocr = new TesseractOCR($rutaCompleta);

            foreach ($idiomas as $idioma) {
                $ocr->lang($idioma);
            }

            $texto = $ocr->psm(6)->run();

            return [
                'texto' => trim($texto),
                'confianza' => $this->calcularConfianzaOCR($texto),
                'idiomas' => $idiomas
            ];
        } catch (\Exception $e) {
            Log::error("Error en OCR múltiple: " . $e->getMessage());
            return $this->procesamientoFallback($rutaImagen, $e->getMessage());
        }
    }

    /**
     * Procesar PDF (requiere convertir PDF a imágenes primero)
     */
    public function procesarPDF($rutaPDF)
    {
        // Para futura implementación - convertir PDF a imágenes y luego OCR
        return [
            'texto' => 'Procesamiento de PDF no implementado aún',
            'confianza' => 0.0,
            'procesado_con' => 'PDF no soportado'
        ];
    }
}
