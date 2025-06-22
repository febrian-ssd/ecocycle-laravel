<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EcoCycle</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        body, html { height: 100%; margin: 0; font-family: 'Nunito', sans-serif; background-color: #f4f4f4; }
        /* ... (CSS LAINNYA YANG SUDAH ADA SEBELUMNYA) ... */
        .page-container { display: flex; height: 100vh; }
        .header { display: flex; align-items: center; justify-content: space-between; padding: 10px 25px; background-color: #004d00; color: white; position: fixed; top: 0; left: 0; right: 0; z-index: 1001; height: 60px; box-sizing: border-box;}
        .header-left { display: flex; align-items: center; }
        .header .logo { display: flex; align-items: center; font-size: 1.5rem; font-weight: bold; }
        .header .logo img { height: 35px; margin-right: 10px; }
        .header .menu-toggle { font-size: 24px; cursor: pointer; margin-right: 15px; }
        .content-area { flex-grow: 1; margin-left: 0; transition: margin-left .3s; padding-top: 60px; }
        .content-area.shifted { margin-left: 250px; }
    </style>
</head>
<body>
    <div id="app" class="page-container">
        @auth
            @if(Auth::user()->is_admin)
                {{-- MEMANGGIL FILE SIDEBAR.BLADE.PHP --}}
                @include('layouts.sidebar')
            @endif
        @endauth

        <div class="content-area" id="content-area">
            <header class="header">
                {{-- ... (kode header Anda tidak berubah) ... --}}
            </header>
            <main class="main-content">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- ... (kode popup login & register tidak berubah) ... --}}

    <script>
        {{-- ... (kode javascript Anda tidak berubah) ... --}}
    </script>
    @yield('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
