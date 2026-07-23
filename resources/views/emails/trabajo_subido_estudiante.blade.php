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
            background-color: #07321e;
            padding: 32px;
            text-align: center;
        }
        .header h1 {
            color: #c2d500;
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
            <h2>Hola, {{ $nombreEstudiante }}!</h2>
            <p>Te informamos que tu trabajo de grado ha sido registrado exitosamente en la plataforma.</p>

            <div class="card">
                <div class="card-title">Trabajo</div>
                <div class="card-body">{{ $trabajo->titulo }}</div>
            </div>

            <div class="card">
                <div class="card-title">Fecha de subida</div>
                <div class="card-body">{{ \Carbon\Carbon::parse($trabajo->fecha_subida)->format('d/m/Y') }}</div>
            </div>

            <div class="card">
                <div class="card-title">Estado</div>
                <div class="card-body">{{ ucfirst($trabajo->estado) }}</div>
            </div>

            <p>El equipo de gestores revisará tu trabajo y pronto recibirás noticias sobre el proceso de evaluación.</p>
        </div>
        <div class="footer">
            Este es un correo automático, por favor no respondas a este mensaje.<br>
            &copy; {{ date('Y') }} Sistema de Gestión de Trabajos de Grado.
        </div>
    </div>
</body>
</html>
