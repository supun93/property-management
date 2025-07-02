<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Welcome | {{ config('app.name', 'Laravel') }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
    <div class="container">
      <a href="#" class="navbar-brand">
        <span class="brand-text font-weight-light">AdminLTE</span>
      </a>

      <div class="collapse navbar-collapse order-3" id="navbarCollapse">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a href="{{ route('login') }}" class="nav-link">Login</a>
          </li>
          <li class="nav-item">
            <a href="{{ route('register') }}" class="nav-link">Register</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Content -->
  <div class="content-wrapper">
    <div class="content">
      <div class="container pt-5">
        <div class="row justify-content-center">
          <div class="col-md-8 text-center">
            <h1>Welcome to Your Laravel + AdminLTE App</h1>
            <p class="lead">You're ready to build something amazing.</p>
            <a href="{{ route('login') }}" class="btn btn-primary btn-lg mt-3">Login</a>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
</body>
</html>
