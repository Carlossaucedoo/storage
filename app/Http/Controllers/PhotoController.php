<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    // Muestra la galería con todas las fotos
    public function index()
    {
        try {
            $photos = Photo::latest()->get();

            return view('pages.photos', ['photos' => $photos]);
        } catch (Exception $exception) {
            // No hay redirección, así que el mensaje se guarda solo para esta petición
            session()->now('error', 'No se han podido cargar las fotos: '.$exception->getMessage());

            return view('pages.photos', ['photos' => collect()]);
        }
    }

    // Sube una foto nueva a storage/app/public/photos
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'imagen' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $ruta = null;

        try {
            $ruta = $request->file('imagen')->store('photos', 'public');

            Photo::create([
                'title' => $request->title,
                'path' => $ruta,
            ]);

            return redirect()->route('photos.index')->with('success', 'Foto subida correctamente.');
        } catch (Exception $exception) {
            // Si el archivo se llegó a guardar pero falló la BD, se borra para no dejarlo huérfano
            if ($ruta) {
                Storage::disk('public')->delete($ruta);
            }

            return back()->withInput()->with('error', 'Error al subir la foto: '.$exception->getMessage());
        }
    }

    // Cambia el título y, si llega una imagen nueva, reemplaza la antigua
    public function update(Request $request, Photo $photo)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $rutaNueva = null;

        try {
            $rutaAntigua = $photo->path;

            if ($request->hasFile('imagen')) {
                $rutaNueva = $request->file('imagen')->store('photos', 'public');
                $photo->path = $rutaNueva;
            }

            $photo->title = $request->title;
            $photo->save();

            // Solo se borra la imagen antigua cuando la nueva ya está guardada
            if ($rutaNueva) {
                Storage::disk('public')->delete($rutaAntigua);
            }

            return redirect()->route('photos.index')->with('success', 'Foto actualizada correctamente.');
        } catch (Exception $exception) {
            if ($rutaNueva) {
                Storage::disk('public')->delete($rutaNueva);
            }

            return back()->with('error', 'Error al actualizar la foto: '.$exception->getMessage());
        }
    }

    // Borra el registro y el archivo físico
    public function destroy(Photo $photo)
    {
        try {
            Storage::disk('public')->delete($photo->path);
            $photo->delete();

            return redirect()->route('photos.index')->with('success', 'Foto eliminada correctamente.');
        } catch (Exception $exception) {
            return back()->with('error', 'Error al eliminar la foto: '.$exception->getMessage());
        }
    }
}
