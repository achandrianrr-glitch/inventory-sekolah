<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Kode OTP</title>
</head>

<body style="font-family:Arial, Helvetica, sans-serif; background:#f6f7fb; margin:0; padding:24px;">
    <div
        style="max-width:560px; margin:0 auto; background:#ffffff; border-radius:12px; padding:20px; border:1px solid #e9ebf3;">
        <h2 style="margin:0 0 12px; font-size:18px;">Verifikasi Email</h2>

        <p style="margin:0 0 10px; color:#333; line-height:1.5;">
            Halo, kamu meminta kode OTP untuk verifikasi email:
            <b>{{ $email }}</b>
        </p>

        <div style="margin:16px 0; padding:14px; background:#0b1220; border-radius:12px; text-align:center;">
            <div style="color:#cbd5e1; font-size:12px; margin-bottom:6px;">Kode OTP kamu</div>
            <div style="color:#ffffff; font-size:28px; letter-spacing:6px; font-weight:700;">
                {{ $otp }}
            </div>
        </div>

        <p style="margin:0 0 8px; color:#333;">
            Kode ini berlaku sampai: <b>{{ $expiresText }}</b>
        </p>

        <p style="margin:0; color:#666; font-size:12px; line-height:1.5;">
            Jika kamu tidak merasa meminta OTP ini, abaikan email ini.
        </p>
    </div>

    <p style="max-width:560px; margin:10px auto 0; color:#94a3b8; font-size:12px; text-align:center;">
        InSchool Inventory
    </p>
</body>

</html>
