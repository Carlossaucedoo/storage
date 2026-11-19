@extends('layouts.app')

@section('titulo', 'Fotos')

@section('contenido')
    <h1>Galería de imágenes</h1>
    <p class="subtitulo">Sube imágenes JPG, PNG, GIF o WEBP de hasta 2 MB.</p>

    <form action="{{ route('photos.store') }}" method="POST" enctype="multipart/form-data" class="formulario-subida">
        @csrf
        <input type="text" name="title" placeholder="Título de la foto" value="{{ old('title') }}" required>
        <input type="file" name="imagen" accept="image/*" required>
        <button type="submit" class="boton">Subir foto</button>
    </form>

    @if ($photos->isEmpty())
        <p class="vacio">Todavía no hay fotos. ¡Sube la primera!</p>
    @else
        <div class="galeria">
            @foreach ($photos as $photo)
                @include('partials.photo', ['photo' => $photo])
            @endforeach
        </div>
    @endif
@endsection
