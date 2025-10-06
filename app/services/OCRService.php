<?php

namespace App\Services;

use App\Models\Manuscrito;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class OCRService
{
    public function procesarImagen($rutaImagen)
    {
        try {
            Log::info('Procesando imagen con OCR', ['ruta_imagen' => $rutaImagen]);
            
            // Verificar que el archivo existe
            if (!Storage::disk('public')->exists($rutaImagen)) {
                throw new Exception("Imagen no encontrada: " . $rutaImagen);
            }
            
            // Obtener la ruta completa del archivo
            $rutaCompleta = Storage::disk('public')->path($rutaImagen);
            
            // Procesar la imagen
            $resultado = $this->procesarImagenConOCR($rutaCompleta);
            
            return [
                'texto' => $resultado['texto'],
                'confianza' => $resultado['confianza'],
                'longitud_texto' => $resultado['longitud_texto'],
                'procesado_con' => $resultado['procesado_con'],
                'palabras_clave' => $resultado['palabras_clave'] ?? [],
                'informacion_estructurada' => $resultado['informacion_estructurada'] ?? []
            ];
            
        } catch (Exception $e) {
            Log::error('Error en procesamiento de imagen OCR: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function procesarImagenConOCR($rutaCompleta)
    {
        // Aquí puedes integrar con diferentes servicios OCR
        
        // Opción 1: Google Cloud Vision API (Recomendado para producción)
        // return $this->usarGoogleVisionOCR($rutaCompleta);
        
        // Opción 2: Tesseract OCR (Open Source)
        // return $this->usarTesseractOCR($rutaCompleta);
        
        // Opción 3: Simulación (para desarrollo)
        return $this->simularOCR($rutaCompleta);
    }
    
    private function simularOCR($rutaCompleta)
    {
        Log::info('Simulando procesamiento OCR para: ' . $rutaCompleta);
        
        // Simular diferentes textos basados en el nombre del archivo o contenido
        $textosEjemplo = [
            "Hoy me siento muy ansioso. Tuve una discusión con mi familia y no puedo dejar de pensar en eso. Me cuesta concentrarme en el trabajo y siento que todo me sale mal.",
            
            "Últimamente he estado teniendo problemas para dormir. Me despierto en la noche con pensamientos negativos y me cuesta volver a conciliar el sueño. Me siento cansado todo el día.",
            
            "Estoy pasando por un momento difícil en mi relación de pareja. Hemos estado discutiendo frecuentemente y siento que ya no nos entendemos como antes.",
            
            "El trabajo me está generando mucho estrés. Mi jefe me exige demasiado y siento que no estoy a la altura de las expectativas. He tenido dolores de cabeza frecuentes.",
            
            "Desde el accidente de auto no he podido recuperar mi normalidad. Tengo pesadillas y me siento irritable la mayor parte del tiempo. Evito manejar cuando puedo."
        ];
        
        // Seleccionar un texto aleatorio para simular
        $textoExtraido = $textosEjemplo[array_rand($textosEjemplo)];
        $confianza = $this->calcularConfianzaSimulada($textoExtraido);
        
        // Extraer información estructurada
        $informacionEstructurada = $this->extraerInformacionEstructurada($textoExtraido);
        
        return [
            'texto' => $textoExtraido,
            'confianza' => $confianza,
            'longitud_texto' => strlen($textoExtraido),
            'procesado_con' => 'OCR Simulado (Desarrollo)',
            'palabras_clave' => $this->extraerPalabrasClave($textoExtraido),
            'informacion_estructurada' => $informacionEstructurada
        ];
    }
    
    private function calcularConfianzaSimulada($texto)
    {
        $longitud = strlen($texto);
        $palabras = str_word_count($texto);
        
        // Simular confianza basada en características del texto
        if ($palabras < 15) return 0.4;
        if ($palabras < 30) return 0.7;
        if ($palabras < 50) return 0.85;
        return 0.95;
    }
    
    private function extraerInformacionEstructurada($texto)
    {
        $textoLower = mb_strtolower($texto);
        
        return [
            'sintomas_detectados' => $this->detectarSintomas($textoLower),
            'contextos' => $this->detectarContextos($textoLower),
            'emociones' => $this->detectarEmociones($textoLower),
            'urgencia' => $this->determinarUrgencia($textoLower),
            'timestamp' => now()->toISOString()
        ];
    }
    
    private function detectarSintomas($texto)
    {
        $sintomas = [
            'ansiedad' => ['ansioso', 'nervioso', 'preocupado', 'pánico', 'angustia', 'miedo', 'tenso'],
            'depresion' => ['triste', 'deprimido', 'desesperanza', 'vacío', 'sin energía', 'desanimado'],
            'estres' => ['estrés', 'agobiado', 'presión', 'sobrecargado', 'quemado'],
            'trauma' => ['trauma', 'abus', 'accidente', 'shock', 'recuerdo intrusivo'],
            'insomnio' => ['no puedo dormir', 'insomnio', 'pesadillas', 'desvelado'],
            'ira' => ['enojado', 'furioso', 'irritable', 'rabia', 'frustrado'],
            'concentracion' => ['concentrarme', 'enfocarme', 'distraído', 'mente en blanco']
        ];
        
        $detectados = [];
        foreach ($sintomas as $sintoma => $palabras) {
            foreach ($palabras as $palabra) {
                if (str_contains($texto, $palabra)) {
                    $detectados[] = $sintoma;
                    break;
                }
            }
        }
        
        return array_unique($detectados);
    }
    
    private function detectarContextos($texto)
    {
        $contextos = [
            'familia' => ['familia', 'padres', 'madre', 'padre', 'hijos', 'hermanos', 'pareja', 'matrimonio'],
            'trabajo' => ['trabajo', 'empleo', 'jefe', 'compañeros', 'oficina', 'despido', 'carrera'],
            'estudios' => ['universidad', 'colegio', 'exámenes', 'estudios', 'profesor'],
            'relaciones' => ['pareja', 'novio', 'novia', 'esposo', 'esposa', 'amigos', 'relación'],
            'salud' => ['enfermedad', 'hospital', 'médico', 'diagnóstico', 'tratamiento'],
            'social' => ['amigos', 'social', 'fiesta', 'reunión', 'solitario']
        ];
        
        $detectados = [];
        foreach ($contextos as $contexto => $palabras) {
            foreach ($palabras as $palabra) {
                if (str_contains($texto, $palabra)) {
                    $detectados[] = $contexto;
                    break;
                }
            }
        }
        
        return array_unique($detectados);
    }
    
    private function detectarEmociones($texto)
    {
        $emociones = [
            'tristeza' => ['triste', 'tristeza', 'llorar', 'desanimado', 'desesperanza'],
            'ansiedad' => ['ansioso', 'nervioso', 'preocupado', 'angustiado', 'asustado'],
            'enojo' => ['enojado', 'furioso', 'molesto', 'irritado', 'frustrado'],
            'miedo' => ['miedo', 'atemorizado', 'asustado', 'pánico', 'terror'],
            'confusion' => ['confundido', 'perdido', 'indeciso', 'no sé qué hacer'],
            'culpa' => ['culpa', 'culpable', 'remordimiento', 'arrepentido']
        ];
        
        $detectados = [];
        foreach ($emociones as $emocion => $palabras) {
            foreach ($palabras as $palabra) {
                if (str_contains($texto, $palabra)) {
                    $detectados[] = $emocion;
                    break;
                }
            }
        }
        
        return array_unique($detectados);
    }
    
    private function determinarUrgencia($texto)
    {
        $palabrasCriticas = ['suicidio', 'matar', 'morir', 'acabar con todo', 'no quiero vivir', 'desesperado'];
        $palabrasUrgentes = ['urgencia', 'emergencia', 'crisis', 'no aguanto más', 'ayuda inmediata'];
        
        foreach ($palabrasCriticas as $palabra) {
            if (str_contains($texto, $palabra)) {
                return 'critico';
            }
        }
        
        foreach ($palabrasUrgentes as $palabra) {
            if (str_contains($texto, $palabra)) {
                return 'alto';
            }
        }
        
        return count($this->detectarSintomas($texto)) > 0 ? 'medio' : 'bajo';
    }
    
    private function extraerPalabrasClave($texto)
    {
        $textoLower = mb_strtolower($texto);
        $stopWords = ['el', 'la', 'los', 'las', 'de', 'del', 'y', 'en', 'un', 'una', 'unos', 'unas', 'con', 'por', 'para', 'sin', 'sobre'];
        
        $palabras = str_word_count($textoLower, 1);
        $palabrasFiltradas = array_diff($palabras, $stopWords);
        
        // Contar frecuencia y devolver las más comunes
        $frecuencia = array_count_values($palabrasFiltradas);
        arsort($frecuencia);
        
        return array_slice(array_keys($frecuencia), 0, 15);
    }

    // Mantener el método procesarManuscrito para compatibilidad
    public function procesarManuscrito(Manuscrito $manuscrito)
    {
        return $this->procesarImagen($manuscrito->imagen_original);
    }
    
    // Métodos para OCR real (puedes implementarlos después)
    
    /**
     * Integración con Google Cloud Vision API
     */
    private function usarGoogleVisionOCR($rutaImagen)
    {
        // Requiere instalar: composer require google/cloud-vision
        /*
        use Google\Cloud\Vision\V1\ImageAnnotatorClient;
        use Google\Cloud\Vision\V1\Image;
        use Google\Cloud\Vision\V1\Feature\Type;
        
        $imageAnnotator = new ImageAnnotatorClient([
            'keyFilePath' => config('services.google_cloud.key_file')
        ]);
        
        $image = new Image();
        $image->setContent(file_get_contents($rutaImagen));
        
        $response = $imageAnnotator->textDetection($image);
        $texts = $response->getTextAnnotations();
        
        $texto = '';
        if ($texts->count() > 0) {
            $texto = $texts[0]->getDescription();
        }
        
        $imageAnnotator->close();
        
        return [
            'texto' => $texto,
            'confianza' => 0.95, // Google Vision proporciona confianza por palabra
            'longitud_texto' => strlen($texto),
            'procesado_con' => 'Google Cloud Vision API'
        ];
        */
        
        // Por ahora, usar simulación
        return $this->simularOCR($rutaImagen);
    }
    
    /**
     * Integración con Tesseract OCR
     */
    private function usarTesseractOCR($rutaImagen)
    {
        // Requiere tener Tesseract instalado en el sistema
        /*
        $tesseract = new TesseractOCR($rutaImagen);
        $tesseract->setLanguage('spa'); // Español
        
        $texto = $tesseract->run();
        
        return [
            'texto' => $texto,
            'confianza' => 0.85,
            'longitud_texto' => strlen($texto),
            'procesado_con' => 'Tesseract OCR'
        ];
        */
        
        // Por ahora, usar simulación
        return $this->simularOCR($rutaImagen);
    }
}