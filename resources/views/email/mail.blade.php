@extends('layouts.email')
@section('content')
    <tr>
        <td style="padding:36px 30px 42px 30px;">
            <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
                <tr>
                    <td style="padding:0 0 36px 0;color:#153643;">
                        <h1 style="font-size:24px;margin:0 0 20px 0;font-family:Arial,sans-serif;">Hi {{ $data['name'] }},</h1>
                        <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">Welcome back. Use the following OTP for verification.</p>
                        <h2 style="background: #000;margin: 0 auto;width: max-content;padding: 0 10px;color: #fff;border-radius: 4px;">{{ $data['otp'] }}</h2><br />
                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">Regards, <br /> Cena</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
@endsection
