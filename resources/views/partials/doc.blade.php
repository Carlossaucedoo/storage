<li class="documento">
    <a href="{{ asset('storage/'.$doc->path) }}" target="_blank" class="documento-enlace">
        <span class="icono-pdf">PDF</span>
        {{ $doc->title }}
    </a>

    <div class="documento-acciones">
        <details>
            <summary>Editar o reemplazar</summary>

            <form action="{{ route('docs.update', $doc) }}" method="POST" enctype="multipart/form-data" class="formulario-editar">
                @csrf
                @method('PUT')
                <input type="text" name="title" value="{{ $doc->title }}" required>
                <input type="file" name="documento" accept="application/pdf">
                <button type="submit" class="boton">Guardar</button>
            </form>
        </details>

        <form action="{{ route('docs.destroy', $doc) }}" method="POST" onsubmit="return confirm('¿Eliminar este documento?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="boton boton-peligro">Eliminar</button>
        </form>
    </div>
</li>
