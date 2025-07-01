<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Property Management System')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CDN (you can switch to Tailwind if you prefer) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4"> 
        <a class="navbar-brand" href="#">🏠 PMS</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="{{ url('/') }}">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('property-categories.index') }}">Property Categories</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="#">{{ auth()->user()->name }}</a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="">
                            @csrf
                            <button class="btn btn-sm btn-outline-light">Logout</button>
                        </form>
                    </li>
                @endauth
                @guest
                    <li class="nav-item"><a class="nav-link" href="">Login</a></li>
                @endguest
            </ul>
        </div>
    </nav>

    <main class="py-4">
        @yield('content')
    </main>

    <!-- Bootstrap JS (Optional for dropdowns/modals) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
