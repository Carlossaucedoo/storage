<?php

namespace App\Http\Controllers;

use App\Models\Doc;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocController extends Controller
{
    // Muestra la lista de documentos
    public function index()
    {
        try {
            $docs = Doc::latest()->get();

            return view('pages.docs', ['docs' => $docs]);
        } catch (Exception $exception) {
            // No hay redirección, así que el mensaje se guarda solo para esta petición
            session()->now('error', 'No se han podido cargar los documentos: '.$exception->getMessage());

            return view('pages.docs', ['docs' => collect()]);
        }
    }

    // Sube un PDF nuevo a storage/app/public/docs
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'documento' => 'required|file|mimes:pdf|max:2048',
        ]);

        $ruta = null;

        try {
            $ruta = $request->file('documento')->store('docs', 'public');

            Doc::create([
                'title' => $request->title,
                'path' => $ruta,
            ]);

            return redirect()->route('docs.index')->with('success', 'Documento subido correctamente.');
        } catch (Exception $exception) {
            // Si el archivo se llegó a guardar pero falló la BD, se borra para no dejarlo huérfano
            if ($ruta) {
                Storage::disk('public')->delete($ruta);
            }

            return back()->withInput()->with('error', 'Error al subir el documento: '.$exception->getMessage());
        }
    }

    // Cambia el título y, si llega un PDF nuevo, reemplaza el antiguo
    public function update(Request $request, Doc $doc)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'documento' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        $rutaNueva = null;

        try {
            $rutaAntigua = $doc->path;

            if ($request->hasFile('documento')) {
                $rutaNueva = $request->file('documento')->store('docs', 'public');
                $doc->path = $rutaNueva;
            }

            $doc->title = $request->title;
            $doc->save();

            // Solo se borra el PDF antiguo cuando el nuevo ya está guardado
            if ($rutaNueva) {
                Storage::disk('public')->delete($rutaAntigua);
            }

            return redirect()->route('docs.index')->with('success', 'Documento actualizado correctamente.');
        } catch (Exception $exception) {
            if ($rutaNueva) {
                Storage::disk('public')->delete($rutaNueva);
            }

            return back()->with('error', 'Error al actualizar el documento: '.$exception->getMessage());
        }
    }

    // Borra el registro y el archivo físico
    public function destroy(Doc $doc)
    {
        try {
            Storage::disk('public')->delete($doc->path);
            $doc->delete();

            return redirect()->route('docs.index')->with('success', 'Documento eliminado correctamente.');
        } catch (Exception $exception) {
            return back()->with('error', 'Error al eliminar el documento: '.$exception->getMessage());
        }
    }
}
