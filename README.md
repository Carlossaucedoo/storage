# Storage · Gestor de archivos en la nube (Laravel 13 + SQLite)

Aplicación con dos secciones conectadas por un menú:

- **/photos** · Galería de imágenes: subir una imagen con título, verla en la página, reemplazarla y eliminarla.
- **/docs** · Gestor de documentos: subir un PDF con título, listarlos, reemplazarlo y eliminarlo. Al pulsar en un documento se abre en una pestaña nueva.

## Instalación

```bash
git clone https://github.com/Carlossaucedoo/storage.git
cd storage
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan storage:link
php artisan serve
```

## Base de datos

Los modelos `Photo` y `Doc` (creados con `php artisan make:model Photo -m`) guardan solo dos campos:

| Campo | Tipo | Ejemplo |
|---|---|---|
| `title` | string | `Playa` |
| `path` | string | `photos/aB3x...png` |

## Almacenamiento

- `php artisan storage:link` crea el enlace `public/storage` → `storage/app/public`.
- Todos los archivos van al disco `public`, cada tipo en su carpeta:
  - Imágenes: `storage/app/public/photos`
  - PDF: `storage/app/public/docs`
- En las vistas se muestran con `asset('storage/'.$photo->path)`.

## Rutas

| Método | URL | Función | Nombre |
|---|---|---|---|
| GET | `/photos` | `PhotoController@index` | `photos.index` |
| POST | `/photos` | `PhotoController@store` | `photos.store` |
| PUT | `/photos/{photo}` | `PhotoController@update` | `photos.update` |
| DELETE | `/photos/{photo}` | `PhotoController@destroy` | `photos.destroy` |
| GET | `/docs` | `DocController@index` | `docs.index` |
| POST | `/docs` | `DocController@store` | `docs.store` |
| PUT | `/docs/{doc}` | `DocController@update` | `docs.update` |
| DELETE | `/docs/{doc}` | `DocController@destroy` | `docs.destroy` |

`/` redirige a `/photos`.

## Controladores

Los dos tienen las 4 funciones y todas usan `try/catch`. Los mensajes de éxito o error se guardan en la sesión (`success` / `error`) y se muestran en `partials/mensajes.blade.php`.

| Función | Qué hace |
|---|---|
| `index` | Lista los registros y carga la vista |
| `store` | Valida, guarda el archivo con `store('photos', 'public')` y crea el registro |
| `update` | Cambia el título y, si llega un archivo nuevo, lo guarda y **borra el antiguo** con `Storage::disk('public')->delete()` |
| `destroy` | **Borra el archivo físico** y después el registro |

Así no quedan archivos huérfanos en el disco. Si falla la base de datos después de guardar un archivo nuevo, el `catch` también lo borra.

## Validación

| Sección | Campo | Reglas |
|---|---|---|
| Fotos | `title` | obligatorio, máx. 255 caracteres |
| Fotos | `imagen` | obligatoria al subir, `image`, jpg/jpeg/png/gif/webp, máx. 2 MB |
| Documentos | `title` | obligatorio, máx. 255 caracteres |
| Documentos | `documento` | obligatorio al subir, `mimes:pdf`, máx. 2 MB |

Al reemplazar, el archivo es opcional: si solo se cambia el título, el archivo se mantiene.

## Formularios

- `enctype="multipart/form-data"` para poder enviar archivos.
- `@csrf` en todos los formularios.
- `@method('PUT')` y `@method('DELETE')` para simular esos métodos, porque HTML solo envía GET y POST.

## Vistas

```
resources/views/
    layouts/app.blade.php      ← estructura común
    pages/photos.blade.php     ← galería
    pages/docs.blade.php       ← documentos
    partials/header.blade.php  ← menú Fotos / Documentos
    partials/footer.blade.php
    partials/mensajes.blade.php
    partials/photo.blade.php   ← tarjeta de cada foto
    partials/doc.blade.php     ← fila de cada documento
public/css/estilos.css
```

## Tests

```bash
php artisan test
```

15 tests (`tests/Feature/PhotoTest.php` y `tests/Feature/DocTest.php`) que comprueban subir, validar formato y tamaño, reemplazar borrando el archivo antiguo y eliminar borrando el archivo.
