<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Actualización de Solicitud - PsyConnect</title>
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
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
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
        .reason-box {
            background: white;
            border-left: 4px solid #ff6b6b;
            padding: 15px;
            margin: 20px 0;
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
        <h1>📋 Actualización de Solicitud</h1>
        <p>Información importante sobre tu registro</p>
    </div>
    
    <div class="content">
        <h2>Hola {{ $profesional->usuario->nombre }},</h2>
        
        <p>Hemos revisado tu solicitud para unirte a PsyConnect como <strong>{{ $profesional->especialidad_principal }}</strong>.</p>
        
        <p>Lamentamos informarte que tu solicitud <strong>no ha sido aprobada</strong> en esta ocasión.</p>

        <div class="reason-box">
            <h3>📝 Motivo del rechazo:</h3>
            <p>{{ $motivoRechazo }}</p>
        </div>

        <p>Si consideras que ha habido un error o deseas más información, puedes:</p>
        <ul>
            <li>📞 Contactar con el administrador de la clínica</li>
            <li>✉️ Enviar documentación adicional si es necesario</li>
            <li>🔄 Volver a enviar tu solicitud con información actualizada</li>
        </ul>

        <p>Si necesitas ayuda con el proceso de registro, no dudes en contactarnos.</p>
        
        <div style="text-align: center;">
            <a href="{{ url('/contacto') }}" class="button">
                Contactar Soporte
            </a>
        </div>

        <p>Saludos cordiales,<br>
        <strong>El equipo de PsyConnect</strong></p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} PsyConnect. Todos los derechos reservados.</p>
        <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
    </div>
</body>
</html>