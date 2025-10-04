<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PsyConnect - Calcular Índice Estado Anímico</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* --- Mantener todos los estilos originales --- */
        * {margin:0;padding:0;box-sizing:border-box;font-family:'Helvetica',Arial,sans-serif;}
        body{background:linear-gradient(135deg,#E6F3FF 0%,#B0E2FF 100%);min-height:100vh;padding:20px;}
        .container{max-width:1200px;margin:0 auto;background:white;border-radius:15px;box-shadow:0 10px 30px rgba(0,0,0,0.1);overflow:hidden;}
        .header{background:linear-gradient(135deg,#87CEEB 0%,#4682B4 100%);color:white;padding:30px;text-align:center;}
        .header h1{font-size:2.5em;margin-bottom:10px;}
        .header .subtitle{font-size:1.2em;opacity:0.9;}
        .content{padding:30px;display:grid;grid-template-columns:1fr 1fr;gap:30px;}
        .upload-section{background:#F8F9FA;padding:25px;border-radius:10px;border:2px dashed #87CEEB;}
        .upload-area{text-align:center;padding:40px 20px;border:2px dashed #ADD8E6;border-radius:10px;cursor:pointer;transition:all 0.3s ease;}
        .upload-area:hover{border-color:#4682B4;background:#E6F3FF;}
        .upload-icon{font-size:3em;color:#87CEEB;margin-bottom:15px;}
        .result-section{background:#F8F9FA;padding:25px;border-radius:10px;}
        .iea-display{text-align:center;padding:30px;background:white;border-radius:10px;box-shadow:0 5px 15px rgba(0,0,0,0.1);}
        .iea-value{font-size:4em;font-weight:bold;color:#4682B4;margin:20px 0;}
        .iea-category{font-size:1.5em;color:#666;margin-bottom:20px;}
        .emotions-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:15px;margin-top:20px;}
        .emotion-item{background:white;padding:15px;border-radius:8px;text-align:center;box-shadow:0 3px 10px rgba(0,0,0,0.1);}
        .emotion-name{font-weight:bold;color:#4682B4;margin-bottom:5px;}
        .emotion-intensity{height:8px;background:#E6F3FF;border-radius:4px;overflow:hidden;}
        .intensity-bar{height:100%;background:linear-gradient(90deg,#87CEEB,#4682B4);border-radius:4px;}
        .button{background:linear-gradient(135deg,#87CEEB 0%,#4682B4 100%);color:white;border:none;padding:15px 30px;border-radius:25px;font-size:1.1em;cursor:pointer;transition:all 0.3s ease;margin:10px 5px;}
        .button:hover{transform:translateY(-2px);box-shadow:0 5px 15px rgba(70,130,180,0.4);}
        .button-secondary{background:#6C757D;}
        .processing-steps{margin-top:20px;}
        .step{display:flex;align-items:center;margin-bottom:15px;padding:15px;background:white;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1);}
        .step-icon{width:40px;height:40px;background:#87CEEB;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;margin-right:15px;}
        .step.completed .step-icon{background:#28A745;}
        .step.processing .step-icon{background:#FFC107;animation:pulse 1.5s infinite;}
        @keyframes pulse{0%{transform:scale(1);}50%{transform:scale(1.1);}100%{transform:scale(1);}}
        .alert-banner{background:linear-gradient(135deg,#FF6B6B 0%,#EE5A24 100%);color:white;padding:20px;border-radius:10px;margin-top:20px;text-align:center;display:none;}
        .feedback-section{margin-top:20px;padding:20px;background:#E6F3FF;border-radius:10px;}
    </style>
</head>

<body>
<div class="container">
    <div class="header">
        <h1>📝 Calcular Índice Estado Anímico (IEA)</h1>
        <div class="subtitle">Sube tu manuscrito para analizar tu estado emocional</div>
    </div>

    <div class="content">
        <!-- Subida -->
        <div class="upload-section">
            <h2>📤 Subir Manuscrito</h2>
            <form action="{{ route('manuscritos.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf
                <div class="upload-area" id="uploadArea">
                    <div class="upload-icon">📄</div>
                    <h3>Arrastra tu imagen aquí o haz clic para seleccionar</h3>
                    <p>Formatos soportados: JPG, PNG, PDF (Máx. 10MB)</p>
                    <input type="file" name="imagen_manuscrito" id="fileInput" accept=".jpg,.jpeg,.png,.pdf" style="display:none;" required>
                </div>

                <div class="processing-steps">
                    <div class="step" id="step1"><div class="step-icon">1</div><div><strong>Procesar OCR</strong><div>Digitalizando texto del manuscrito...</div></div></div>
                    <div class="step" id="step2"><div class="step-icon">2</div><div><strong>Analizar emociones</strong><div>Identificando patrones emocionales...</div></div></div>
                    <div class="step" id="step3"><div class="step-icon">3</div><div><strong>Calcular IEA</strong><div>Generando índice de estado anímico...</div></div></div>
                    <div class="step" id="step4"><div class="step-icon">4</div><div><strong>Guardar resultados</strong><div>Almacenando en base de datos...</div></div></div>
                </div>

                <div class="alert-banner" id="alertBanner">
                    <h3>🚨 Alerta: IEA Crítico Detectado</h3>
                    <p>Tu índice de estado anímico requiere atención inmediata. Se ha notificado a tu profesional.</p>
                    <button type="button" class="button" onclick="showCrisisResources()">Ver Recursos de Ayuda</button>
                </div>

                <div style="text-align:center;margin-top:20px;">
                    <button type="button" class="button" onclick="processManuscript()" id="processBtn" disabled>Iniciar Análisis</button>
                    <button type="submit" class="button button-secondary" id="saveBtn" disabled style="display:none;">Guardar en Historial</button>
                </div>
            </form>
        </div>

        <!-- Resultados -->
        <div class="result-section">
            <h2>📊 Resultado del Análisis</h2>
            <div class="iea-display">
                <div class="iea-value" id="ieaValue">--</div>
                <div class="iea-category" id="ieaCategory">Esperando análisis...</div>
                <div class="emotions-grid" id="emotionsGrid"></div>
                <div class="feedback-section">
                    <h4>💡 Feedback del Sistema</h4>
                    <p id="feedbackText">Sube un manuscrito para recibir feedback personalizado sobre tu estado emocional.</p>
                    <p id="keywordCounter" class="mt-2 text-sm text-red-600"></p>
                </div>
            </div>

            <div id="realResults" style="display:none;">
                <div style="text-align:center;margin-top:20px;">
                    <a href="{{ route('manuscritos.index') }}" class="button"><i class="fas fa-list me-2"></i>Ver Todos mis Manuscritos</a>
                    <a href="{{ route('dashboard') }}" class="button button-secondary"><i class="fas fa-chart-line me-2"></i>Ir al Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentFile = null;
let formData = new FormData();

// Subida de archivos
document.getElementById('uploadArea').addEventListener('click',()=>document.getElementById('fileInput').click());
document.getElementById('fileInput').addEventListener('change',(e)=>{
    const file=e.target.files[0];
    if(file){
        currentFile=file;
        formData=new FormData();
        formData.append('imagen_manuscrito',file);
        formData.append('_token','{{ csrf_token() }}');
        document.getElementById('processBtn').disabled=false;
        document.getElementById('uploadArea').innerHTML=`
            <div class="upload-icon">✅</div>
            <h3>Archivo listo: ${file.name}</h3>
            <p>Tamaño: ${(file.size/1024/1024).toFixed(2)} MB</p>
            <button type="button" class="button" onclick="clearFile()">Cambiar archivo</button>
        `;
    }
});

function clearFile(){
    currentFile=null;
    formData=new FormData();
    document.getElementById('fileInput').value='';
    document.getElementById('processBtn').disabled=true;
    document.getElementById('uploadArea').innerHTML=`
        <div class="upload-icon">📄</div>
        <h3>Arrastra tu imagen aquí o haz clic para seleccionar</h3>
        <p>Formatos soportados: JPG, PNG, PDF (Máx. 10MB)</p>
        <input type="file" name="imagen_manuscrito" id="fileInput" accept=".jpg,.jpeg,.png,.pdf" style="display:none;" required>
    `;
}

// Procesamiento con AJAX
async function processManuscript(){
    if(!currentFile){alert('Por favor, selecciona un archivo primero');return;}
    const steps=document.querySelectorAll('.step');
    const processBtn=document.getElementById('processBtn');
    processBtn.disabled=true;
    processBtn.textContent='Procesando...';
    steps.forEach(step=>step.classList.remove('processing','completed'));

    try{
        // Paso 1
        steps[0].classList.add('processing');
        const response=await fetch('{{ route('manuscritos.store') }}',{method:'POST',body:formData,headers:{'X-Requested-With':'XMLHttpRequest'}});
        const responseText=await response.text();
        let result;
        try{result=JSON.parse(responseText);}catch(e){throw new Error('Error al parsear JSON');}
        if(!response.ok||!result.success){throw new Error(result.message||`Error del servidor: ${response.status}`);}
        await simulateProcessing(1000);steps[0].classList.remove('processing');steps[0].classList.add('completed');
        // Paso 2
        steps[1].classList.add('processing');await simulateProcessing(2000);steps[1].classList.remove('processing');steps[1].classList.add('completed');
        // Paso 3
        steps[2].classList.add('processing');await simulateProcessing(1500);steps[2].classList.remove('processing');steps[2].classList.add('completed');
        // Paso 4
        steps[3].classList.add('processing');await simulateProcessing(1000);steps[3].classList.remove('processing');steps[3].classList.add('completed');

        displayRealResults(result);
        document.getElementById('realResults').style.display='block';
        processBtn.textContent='Análisis Completado';processBtn.disabled=true;
    }catch(error){
        console.error(error);steps.forEach(step=>step.classList.remove('processing','completed'));
        alert('Error al procesar el manuscrito: '+error.message);
        processBtn.textContent='Reintentar';processBtn.disabled=false;
    }
}

function simulateProcessing(ms){return new Promise(resolve=>setTimeout(resolve,ms));}

function displayRealResults(result){
    const iea=result.iea;
    document.getElementById('ieaValue').textContent=iea.valor_numerico;
    document.getElementById('ieaCategory').textContent=iea.categoria_emotional;

    let feedbackHTML=generarFeedback(iea.valor_numerico);
    if(iea.emocion_principal){
        feedbackHTML+=`<div class="mt-4 p-3 bg-blue-50 rounded-lg">
            <strong>🎭 Emoción Principal:</strong> ${capitalizeFirst(iea.emocion_principal)}<br>
            <strong>📊 Intensidad:</strong> ${iea.intensidad_principal}%<br>
            <strong>📝 Resumen:</strong> ${iea.resumen_analisis || 'Análisis completado'}
        </div>`;
    }
    document.getElementById('feedbackText').innerHTML=feedbackHTML;

    // Mostrar emociones
    const emotionsGrid=document.getElementById('emotionsGrid');
    emotionsGrid.innerHTML=`
        <div class="emotion-item">
            <div class="emotion-name">${capitalizeFirst(iea.emocion_principal||'neutral')}</div>
            <div class="emotion-intensity">
                <div class="intensity-bar" style="width:${iea.intensidad_principal||50}%"></div>
            </div>
            <div style="font-size:0.8em;margin-top:5px;">${iea.intensidad_principal||50}%</div>
        </div>
        <div class="emotion-item">
            <div class="emotion-name">Confianza</div>
            <div class="emotion-intensity">
                <div class="intensity-bar" style="width:${iea.confiabilidad_analisis||70}%"></div>
            </div>
            <div style="font-size:0.8em;margin-top:5px;">${iea.confiabilidad_analisis||70}%</div>
        </div>
    `;

    // Alerta crítica
    if(iea.valor_numerico<20){document.getElementById('alertBanner').style.display='block';}

    // --- Contador de palabras clave ---
    if(iea.texto_completo){
        const keywords=['tristeza','ansiedad','estrés','crisis','depresión','angustia'];
        let counts={};
        keywords.forEach(k=>{const regex=new RegExp(`\\b${k}\\b`,'gi');counts[k]=(iea.texto_completo.match(regex)||[]).length;});
        let counterHTML='📌 Palabras críticas detectadas:<br>';
        for(const k in counts){if(counts[k]>0){counterHTML+=`- ${k}: ${counts[k]} veces<br>`;}}
        document.getElementById('keywordCounter').innerHTML=counterHTML;
    }else{document.getElementById('keywordCounter').innerHTML='';}

    document.getElementById('realResults').style.display='block';
}

function capitalizeFirst(s){return s.charAt(0).toUpperCase()+s.slice(1);}
function generarFeedback(ieaValue){
    if(ieaValue<20) return 'Tu estado emocional requiere atención inmediata. Por favor contacta a tu profesional de confianza.';
    else if(ieaValue<40) return 'Parece que estás pasando por un momento difícil. Te recomendamos actividades de autocuidado y contacto con tu red de apoyo.';
    else if(ieaValue<60) return 'Tu estado emocional se encuentra equilibrado. Continúa con tus prácticas de bienestar y autorreflexión.';
    else if(ieaValue<80) return '¡Excelente! Tu estado emocional es positivo. Aprovecha esta energía para seguir progresando.';
    else return '¡Fantástico! Tu bienestar emocional está en su mejor momento. Sigue cultivando estas emociones positivas.';
}
function showCrisisResources(){alert('Recursos de crisis:\n\n• Línea de Prevención del Suicidio: 911\n• Centro de Atención Psicológica: 0800-222-1133\n• Ejercicios de respiración y grounding disponibles en tu dashboard');}

// Drag & Drop
const uploadArea=document.getElementById('uploadArea');
uploadArea.addEventListener('dragover',e=>{e.preventDefault();uploadArea.style.borderColor='#4682B4';uploadArea.style.background='#E6F3FF';});
uploadArea.addEventListener('dragleave',()=>{uploadArea.style.borderColor='#ADD8E6';uploadArea.style.background='';});
uploadArea.addEventListener('drop',e=>{e.preventDefault();const files=e.dataTransfer.files;if(files.length>0){const file=files[0];if(!file.type.match('image.*')){alert('Por favor, selecciona solo archivos de imagen (JPG, PNG)');return;}const dt=new DataTransfer();dt.items.add(file);document.getElementById('fileInput').files=dt.files;document.getElementById('fileInput').dispatchEvent(new Event('change',{bubbles:true}));}});
console.log('Script de manuscritos cargado correctamente');
</script>
</body>
</html>
