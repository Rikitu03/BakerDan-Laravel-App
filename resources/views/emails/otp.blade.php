<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BakerDan Verification Code</title>
</head>
<body style="margin: 0; padding: 0; background: #fcf9f2; color: #1c1c18; font-family: Arial, Helvetica, sans-serif;">
    <div style="display: none; max-height: 0; overflow: hidden; opacity: 0;">
        Your BakerDan verification code is {{ $otp }}. It expires in {{ $expiresInMinutes ?? 10 }} minutes.
    </div>

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background: #fcf9f2; padding: 48px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="width: 100%; max-width: 600px; background: #fffdf8; border: 1px solid #dac1ba; border-radius: 12px; overflow: hidden; box-shadow: 0 18px 48px rgba(49, 49, 44, 0.08);">
                    <tr>
                        <td align="center" style="padding: 40px 32px 30px; border-bottom: 1px solid #eadbd6;">
                            <div style="font-family: Georgia, 'Times New Roman', serif; font-size: 30px; line-height: 1.2; font-weight: 700; letter-spacing: 8px; color: #1c1c18;">
                                BAKERDAN
                            </div>
                            <div style="margin-top: 10px; font-size: 12px; line-height: 1.4; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; color: #c1694f;">
                                Wholesale Bakery
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 42px 40px 38px;">
                            <h1 style="margin: 0 0 16px; font-family: Georgia, 'Times New Roman', serif; font-size: 28px; line-height: 1.25; font-weight: 600; color: #1c1c18;">
                                Hello,
                            </h1>

                            <p style="margin: 0 0 28px; font-size: 16px; line-height: 1.7; color: #55433e;">
                                We received a request to verify your account. Please use the one-time passcode below to confirm your identity.
                            </p>

                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 28px; background: #f6f3ec; border: 1px solid #dac1ba; border-radius: 10px;">
                                <tr>
                                    <td align="center" style="padding: 30px 20px;">
                                        <div style="margin-bottom: 14px; font-size: 12px; line-height: 1.4; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #c1694f;">
                                            Verification Code
                                        </div>
                                        <div style="font-family: Georgia, 'Times New Roman', serif; font-size: 44px; line-height: 1.15; font-weight: 700; letter-spacing: 10px; color: #1c1c18;">
                                            {{ $otp }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0; font-size: 14px; line-height: 1.7; color: #55433e;">
                                This code is valid for {{ $expiresInMinutes ?? 10 }} minutes. If you did not request this, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 30px 40px; border-top: 1px solid #eadbd6; background: #ffffff;">
                            <p style="margin: 0 0 12px; font-size: 14px; line-height: 1.7; color: #55433e;">
                                <strong style="font-family: Georgia, 'Times New Roman', serif; color: #1c1c18;">BakerDan Wholesale Bakery</strong><br>
                                Delivering consistent quality and freshness you can trust.
                            </p>
                            <p style="margin: 0; font-size: 12px; line-height: 1.6; color: #87726d;">
                                Questions? Contact us at
                                <a href="mailto:{{ config('mail.from.address') }}" style="color: #93452e; text-decoration: none;">{{ config('mail.from.address') }}</a>
                            </p>
                        </td>
                    </tr>
                </table>

                <div style="width: 100%; max-width: 600px; margin-top: 24px; font-size: 12px; line-height: 1.6; color: #87726d; text-align: center;">
                    &copy; {{ now()->year }} BakerDan. All rights reserved.
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
