<style type="text/css">
  body,
  html, 
  .body {
    background: #f3f3f3 !important;
  }
  .header {
    background: #f3f3f3;
  }
</style>
<!-- move the above styles into your custom stylesheet -->

<spacer size="16"></spacer>

<container>

  <row class="header">
    <columns>

      <spacer size="16"></spacer>
      
      <h2 class="text-center">Hi, {{ $data['name'] }}</h2>
    </columns>
  </row>
  <row>
    <columns>

      <h1 class="text-center">Forgot Your Password?</h1>
      
      <spacer size="16"></spacer>

      <p class="text-center">Your one time password is : {{ $data['otp'] }}</p>
        

    </columns>
  </row>

  <spacer size="16"></spacer>
</container>