<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solicitud Aprobada - PsyConnect</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 ¡Felicidades!</h1>
        <p>Tu solicitud ha sido aprobada</p>
    </div>
    
    <div class="content">
        <h2>Hola {{ $profesional->usuario->nombre }},</h2>
        
        <p>Nos complace informarte que tu solicitud para unirte a PsyConnect como <strong>{{ $profesional->especialidad_principal }}</strong> ha sido <strong>aprobada</strong>.</p>
        
        <p>A partir de ahora podrás:</p>
        <ul>
            <li>✅ Recibir asignaciones de pacientes</li>
            <li>✅ Gestionar tu perfil profesional</li>
            <li>✅ Configurar tu disponibilidad</li>
            <li>✅ Utilizar todas las herramientas de la plataforma</li>
        </ul>

        <p>Tu clínica asignada es: <strong>{{ $profesional->clinicas->first()->nombre ?? 'Por asignar' }}</strong></p>
        
        <div style="text-align: center;">
            <a href="{{ url('/dashboard') }}" class="button">
                Acceder a Mi Dashboard
            </a>
        </div>

        <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
        
        <p>Saludos cordiales,<br>
        <strong>El equipo de PsyConnect</strong></p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} PsyConnect. Todos los derechos reservados.</p>
        <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
    </div>
</body>
</html>