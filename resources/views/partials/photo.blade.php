<article class="tarjeta">
    <img src="{{ asset('storage/'.$photo->path) }}" alt="{{ $photo->title }}" class="tarjeta-imagen">

    <div class="tarjeta-cuerpo">
        <h3>{{ $photo->title }}</h3>

        <details>
            <summary>Editar o reemplazar</summary>

            <form action="{{ route('photos.update', $photo) }}" method="POST" enctype="multipart/form-data" class="formulario-editar">
                @csrf
                @method('PUT')
                <input type="text" name="title" value="{{ $photo->title }}" required>
                <input type="file" name="imagen" accept="image/*">
                <button type="submit" class="boton">Guardar</button>
            </form>
        </details>

        <form action="{{ route('photos.destroy', $photo) }}" method="POST" onsubmit="return confirm('¿Eliminar esta foto?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="boton boton-peligro">Eliminar</button>
        </form>
    </div>
</article>
