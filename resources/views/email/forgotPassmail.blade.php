@extends('layouts.email')

@section('content')
 <p style="font-size:1.1em">Hi,</p>
    <p>Thank you for choosing Cena. Use the following OTP to complete your process. </p>
    <h2 style="background: #00466a;margin: 0 auto;width: max-content;padding: 0 10px;color: #fff;border-radius: 4px;">{{ $data['otp'] }}</h2>
    <p style="font-size:0.9em;">Regards,<br />Cena</p>
    <hr style="border:none;border-top:1px solid #eee" />

@endsection