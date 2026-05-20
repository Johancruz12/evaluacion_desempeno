<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autoevaluación completada</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .wrapper { max-width: 600px; margin: 40px auto; padding: 0 16px; }
        .card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #2563eb, #0ea5e9); padding: 36px 32px; text-align: center; }
        .header-icon { width: 56px; height: 56px; background: rgba(255,255,255,0.2); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; }
        .header h1 { color: #ffffff; font-size: 22px; font-weight: 700; margin: 0 0 6px; }
        .header p { color: rgba(255,255,255,0.85); font-size: 14px; margin: 0; }
        .body { padding: 36px 32px; }
        .greeting { font-size: 16px; color: #1e293b; margin-bottom: 20px; }
        .info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; margin-bottom: 24px; }
        .info-row { display: flex; gap: 12px; margin-bottom: 12px; align-items: flex-start; }
        .info-row:last-child { margin-bottom: 0; }
        .info-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; min-width: 110px; padding-top: 1px; }
        .info-value { font-size: 14px; color: #1e293b; font-weight: 600; }
        .message { font-size: 15px; color: #475569; line-height: 1.6; margin-bottom: 28px; }
        .cta-wrapper { text-align: center; margin-bottom: 28px; }
        .cta-btn { display: inline-block; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #ffffff !important; text-decoration: none; font-size: 15px; font-weight: 700; padding: 14px 32px; border-radius: 12px; letter-spacing: 0.01em; }
        .note { background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 14px 18px; font-size: 13px; color: #92400e; margin-bottom: 24px; }
        .footer { padding: 20px 32px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            <div class="header-icon">
                <svg width="28" height="28" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1>Autoevaluación completada</h1>
            <p>Se requiere tu calificación como jefe de área</p>
        </div>
        <div class="body">
            <p class="greeting">Hola, <strong>{{ $jefeName }}</strong>.</p>

            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Empleado</span>
                    <span class="info-value">{{ $employeeName }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Evaluación</span>
                    <span class="info-value">{{ $templateName }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Período</span>
                    <span class="info-value">{{ $period }}</span>
                </div>
            </div>

            <p class="message">
                <strong>{{ $employeeName }}</strong> acaba de completar su autoevaluación.
                Ahora te corresponde a ti revisar sus respuestas y calificar cada criterio
                como jefe de área. La <strong>nota final del empleado</strong> se generará
                automáticamente una vez que ambas partes (autoevaluación + calificación del jefe)
                estén completas.
            </p>

            <div class="cta-wrapper">
                <a href="{{ $evaluationUrl }}" class="cta-btn">
                    Completar evaluación →
                </a>
            </div>

            <div class="note">
                ⏱ <strong>Recuerda:</strong> Si la evaluación tiene fecha límite de cierre,
                debes completar tu calificación antes de que expire. Después no podrás hacer cambios.
            </div>
        </div>
        <div class="footer">
            Este correo fue generado automáticamente por el sistema de evaluación. No respondas a este mensaje.
        </div>
    </div>
</div>
</body>
</html>
