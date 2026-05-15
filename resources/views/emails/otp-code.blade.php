<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Código de verificación — JUnical</title>
</head>
<body style="margin:0;padding:0;background:#eef2ff;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Inter,Arial,sans-serif;color:#0f172a;">

    <!-- Wrapper -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:linear-gradient(135deg,#eef2ff 0%,#dbeafe 50%,#e0f2fe 100%);padding:40px 16px;">
        <tr>
            <td align="center">
                <!-- Card -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:520px;background:#ffffff;border-radius:24px;overflow:hidden;box-shadow:0 20px 60px rgba(30,58,138,.18),0 4px 12px rgba(2,6,23,.06);">

                    <!-- Header con gradiente -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#2563eb 0%,#3b82f6 50%,#0ea5e9 100%);padding:36px 32px 32px;text-align:center;position:relative;">
                            <!-- Logo circular -->
                            <div style="display:inline-block;width:72px;height:72px;background:rgba(255,255,255,.18);border:2px solid rgba(255,255,255,.35);border-radius:20px;line-height:72px;margin-bottom:14px;backdrop-filter:blur(8px);">
                                <span style="font-size:34px;line-height:72px;">🔐</span>
                            </div>
                            <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:800;letter-spacing:-.5px;">
                                Código de verificación
                            </h1>
                            <p style="margin:6px 0 0;color:#dbeafe;font-size:13px;font-weight:500;">
                                JUnical · Evaluación de Desempeño
                            </p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:36px 36px 8px;">
                            <p style="margin:0 0 8px;color:#0f172a;font-size:18px;font-weight:700;">
                                Hola, {{ $name }} 👋
                            </p>
                            <p style="margin:0 0 28px;color:#475569;font-size:14px;line-height:1.6;">
                                Recibimos una solicitud para verificar tu identidad en el sistema de evaluación de desempeño. Usa el siguiente código para continuar:
                            </p>

                            <!-- Código destacado -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 28px;">
                                <tr>
                                    <td style="background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 50%,#0284c7 100%);border-radius:18px;padding:28px 20px;text-align:center;box-shadow:0 12px 32px rgba(37,99,235,.35);">
                                        <p style="margin:0 0 8px;color:rgba(255,255,255,.75);font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;">
                                            Tu código de acceso
                                        </p>
                                        <div style="font-family:'Courier New',Consolas,monospace;color:#ffffff;font-size:42px;font-weight:800;letter-spacing:12px;line-height:1;text-shadow:0 2px 8px rgba(0,0,0,.2);">
                                            {{ $code }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Info de validez -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px;">
                                <tr>
                                    <td style="background:#fef3c7;border-left:4px solid #f59e0b;border-radius:10px;padding:14px 16px;">
                                        <p style="margin:0;color:#92400e;font-size:13px;font-weight:600;">
                                            ⏱ Válido por <strong>{{ $ttlMinutes }} minutos</strong>
                                        </p>
                                        <p style="margin:4px 0 0;color:#b45309;font-size:12px;">
                                            Solo puede usarse una vez. Si expira, solicita uno nuevo desde el login.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Aviso de seguridad -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="background:#f1f5f9;border-radius:10px;padding:14px 16px;">
                                        <p style="margin:0;color:#475569;font-size:12px;line-height:1.5;">
                                            🛡 <strong style="color:#1e293b;">¿No solicitaste este código?</strong><br>
                                            Ignora este mensaje y contacta de inmediato al departamento de Recursos Humanos. Nunca compartas este código con nadie.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:24px 36px 32px;border-top:1px solid #e2e8f0;background:#fafbfc;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center">
                                        <p style="margin:0 0 4px;color:#1e3a8a;font-size:13px;font-weight:700;">
                                            Clínica Junical
                                        </p>
                                        <p style="margin:0;color:#94a3b8;font-size:11px;">
                                            Sistema de Evaluación de Desempeño · Mensaje automático
                                        </p>
                                        <p style="margin:8px 0 0;color:#cbd5e1;font-size:10px;">
                                            © {{ date('Y') }} JUnical · Todos los derechos reservados
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Espaciador inferior -->
                <p style="margin:20px 0 0;color:#94a3b8;font-size:11px;text-align:center;">
                    Este es un mensaje automático, por favor no respondas a este correo.
                </p>
            </td>
        </tr>
    </table>

</body>
</html>

