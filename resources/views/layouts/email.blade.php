<!DOCTYPE html>
<html lang="en">
<head>
  <title>Email</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="background-color:#E1E1E1; font-family: arial">
<div style=' width:700px; margin:0 auto'>
    <div style='background:#fff; border:#888 solid 1px; border-radius:15px; margin-top:20px; padding:20px; box-shadow:0 0 8px #999'>
        <div style='font-size: 16px ; font-style: italic ; border-bottom: 1px solid #ccc ; padding-bottom: 0px;text-align: center;padding: 10px 0;border-radius: 12px;'>
            <img src="{{ asset('/public/assets/images/logo.png') }}" alt="cena" style="width:90px;">
            
        </div>
        <div style='font-size:14px; padding-top:20px; word-break: break-word;'>
            @yield('content')
        </div>
    </div>
</div>

</body>
</html>

