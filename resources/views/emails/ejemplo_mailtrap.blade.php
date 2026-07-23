<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación del Sistema de Grado</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f5;
            color: #3f3f46;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e4e4e7;
        }
        .header {
            background-color: #07321e; /* Color principal de la app */
            padding: 32px;
            text-align: center;
        }
        .header h1 {
            color: #c2d500; /* Color secundario de la app */
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .content {
            padding: 40px 32px;
        }
        .content h2 {
            color: #18181b;
            font-size: 20px;
            margin-top: 0;
            margin-bottom: 16px;
            font-weight: 700;
        }
        .content p {
            font-size: 15px;
            line-height: 1.6;
            color: #52525b;
            margin-bottom: 24px;
        }
        .card {
            background-color: #f9fafb;
            border: 1px solid #f3f4f6;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .card-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #9ca3af;
            margin-bottom: 8px;
        }
        .card-body {
            font-size: 15px;
            color: #1f2937;
            font-weight: 600;
        }
        .button-container {
            text-align: center;
            margin-top: 32px;
        }
        .button {
            display: inline-block;
            background-color: #c2d500;
            color: #07321e !important;
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
            transition: background-color 0.2s;
        }
        .footer {
            background-color: #fafafa;
            padding: 24px 32px;
            text-align: center;
            border-t: 1px solid #f3f4f6;
            font-size: 12px;
            color: #a1a1aa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Sistema de Trabajos de Grado</h1>
        </div>
        <div class="content">
            <h2>¡Hola, {{ $destinatario }}!</h2>
            <p>Este es un correo electrónico de prueba enviado utilizando <strong>Mailtrap</strong> para validar la correcta integración de los envíos de notificaciones por email en la plataforma.</p>
            
            <div class="card">
                <div class="card-title">Detalle del Mensaje</div>
                <div class="card-body">
                    {{ $mensajePersonalizado }}
                </div>
            </div>

            <p>Si has recibido este mensaje correctamente, significa que las credenciales SMTP en tu archivo de configuración .env están sincronizadas con tu Sandbox de Mailtrap.</p>

            <div class="button-container">
                <a href="{{ url('/') }}" class="button">Ir a la Plataforma</a>
            </div>
        </div>
        <div class="footer">
            Este es un correo automático de prueba. Por favor, no respondas a este mensaje.<br>
            &copy; {{ date('Y') }} Sistema de Gestión de Trabajos de Grado.
        </div>
    </div>
</body>
</html>
