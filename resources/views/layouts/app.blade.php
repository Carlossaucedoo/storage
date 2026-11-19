<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo') · Storage</title>
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
</head>
<body>
    @include('partials.header')

    <main class="contenedor">
        @include('partials.mensajes')

        @yield('contenido')
    </main>

    @include('partials.footer')
</body>
</html>
