<!DOCTYPE html>
<html lang="es">
<body style="font-family:Inter,Arial,sans-serif;background:#f1f5f9;padding:24px;color:#0f172a">
    <div style="max-width:480px;margin:auto;background:#fff;border-radius:16px;padding:28px;box-shadow:0 4px 24px rgba(2,6,23,.06)">
        <h2 style="margin:0 0 8px;color:#1e3a8a">Hola, {{ $name }}</h2>
        <p style="margin:0 0 16px;color:#475569;font-size:14px">
            Recibimos una solicitud para verificar tu identidad en el sistema de evaluación de desempeño.
            Usa este código:
        </p>

        <div style="background:linear-gradient(135deg,#3b82f6,#6366f1);color:#fff;border-radius:12px;padding:18px;text-align:center;letter-spacing:8px;font-size:32px;font-weight:700;margin:18px 0">
            {{ $code }}
        </div>

        <p style="margin:0 0 8px;font-size:13px;color:#475569">
            El código es válido por <strong>{{ $ttlMinutes }} minutos</strong> y solo puede usarse una vez.
        </p>
        <p style="margin:0;font-size:12px;color:#94a3b8">
            Si no solicitaste este código, ignora este mensaje y contacta a Recursos Humanos.
        </p>
    </div>
</body>
</html>
