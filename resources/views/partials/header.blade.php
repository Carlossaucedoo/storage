<header class="cabecera">
    <div class="contenedor cabecera-interior">
        <a href="{{ route('home') }}" class="logo">☁ Storage</a>

        <nav class="menu">
            <a href="{{ route('photos.index') }}" class="{{ request()->routeIs('photos.*') ? 'activo' : '' }}">Fotos</a>
            <a href="{{ route('docs.index') }}" class="{{ request()->routeIs('docs.*') ? 'activo' : '' }}">Documentos</a>
        </nav>
    </div>
</header>
