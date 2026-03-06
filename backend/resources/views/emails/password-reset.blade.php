<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:system-ui,-apple-system,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:500px;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background:linear-gradient(135deg,#4f46e5,#7c3aed);padding:32px;text-align:center;">
                            <h1 style="margin:0;color:#fff;font-size:24px;font-weight:700;">TechStore</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h2 style="margin:0 0 16px;color:#111827;font-size:20px;">Reset Your Password</h2>
                            <p style="margin:0 0 24px;color:#6b7280;font-size:15px;line-height:1.6;">
                                Hi {{ $userName }}, we received a request to reset your password. Click the button below to set a new one.
                            </p>
                            <table cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
                                <tr>
                                    <td style="background:#4f46e5;border-radius:8px;">
                                        <a href="{{ $resetUrl }}" style="display:inline-block;padding:14px 32px;color:#fff;text-decoration:none;font-size:15px;font-weight:600;">
                                            Reset Password
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 8px;color:#9ca3af;font-size:13px;">This link expires in 60 minutes.</p>
                            <p style="margin:0;color:#9ca3af;font-size:13px;">If you didn't request this, you can safely ignore this email.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 32px;background:#f9fafb;border-top:1px solid #e5e7eb;text-align:center;">
                            <p style="margin:0;color:#9ca3af;font-size:12px;">&copy; {{ date('Y') }} TechStore. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
