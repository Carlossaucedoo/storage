@extends('layouts.app')

@section('titulo', 'Documentos')

@section('contenido')
    <h1>Gestor de documentos</h1>
    <p class="subtitulo">Sube documentos PDF de hasta 2 MB. Pulsa en uno para abrirlo en una pestaña nueva.</p>

    <form action="{{ route('docs.store') }}" method="POST" enctype="multipart/form-data" class="formulario-subida">
        @csrf
        <input type="text" name="title" placeholder="Título del documento" value="{{ old('title') }}" required>
        <input type="file" name="documento" accept="application/pdf" required>
        <button type="submit" class="boton">Subir PDF</button>
    </form>

    @if ($docs->isEmpty())
        <p class="vacio">Todavía no hay documentos. ¡Sube el primero!</p>
    @else
        <ul class="lista-docs">
            @foreach ($docs as $doc)
                @include('partials.doc', ['doc' => $doc])
            @endforeach
        </ul>
    @endif
@endsection
