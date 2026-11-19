<?php

namespace Tests\Feature;

use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_la_galeria_carga(): void
    {
        $this->get('/photos')->assertOk()->assertSee('Galería de imágenes');
    }

    public function test_sube_una_foto(): void
    {
        $this->post('/photos', [
            'title' => 'Playa',
            'imagen' => UploadedFile::fake()->image('playa.jpg'),
        ])->assertRedirect('/photos')->assertSessionHas('success');

        $photo = Photo::first();
        $this->assertEquals('Playa', $photo->title);
        $this->assertStringStartsWith('photos/', $photo->path);
        Storage::disk('public')->assertExists($photo->path);

        $this->get('/photos')->assertSee('storage/'.$photo->path);
    }

    public function test_no_acepta_archivos_que_no_son_imagen(): void
    {
        $this->post('/photos', [
            'title' => 'Falsa',
            'imagen' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('imagen');

        $this->assertDatabaseCount('photos', 0);
    }

    public function test_titulo_e_imagen_son_obligatorios(): void
    {
        $this->post('/photos', [])->assertSessionHasErrors(['title', 'imagen']);
    }

    public function test_no_acepta_imagenes_de_mas_de_2mb(): void
    {
        $this->post('/photos', [
            'title' => 'Grande',
            'imagen' => UploadedFile::fake()->image('grande.jpg')->size(3000),
        ])->assertSessionHasErrors('imagen');
    }

    public function test_reemplazar_borra_la_imagen_antigua(): void
    {
        $this->post('/photos', ['title' => 'Vieja', 'imagen' => UploadedFile::fake()->image('vieja.jpg')]);
        $photo = Photo::first();
        $rutaAntigua = $photo->path;

        $this->put("/photos/{$photo->id}", [
            'title' => 'Nueva',
            'imagen' => UploadedFile::fake()->image('nueva.png'),
        ])->assertRedirect('/photos')->assertSessionHas('success');

        $photo->refresh();
        $this->assertEquals('Nueva', $photo->title);
        Storage::disk('public')->assertMissing($rutaAntigua);
        Storage::disk('public')->assertExists($photo->path);
    }

    public function test_actualizar_solo_el_titulo_mantiene_la_imagen(): void
    {
        $this->post('/photos', ['title' => 'Vieja', 'imagen' => UploadedFile::fake()->image('vieja.jpg')]);
        $photo = Photo::first();

        $this->put("/photos/{$photo->id}", ['title' => 'Otro título'])->assertSessionHas('success');

        $this->assertEquals('Otro título', $photo->fresh()->title);
        Storage::disk('public')->assertExists($photo->path);
    }

    public function test_eliminar_borra_registro_y_archivo(): void
    {
        $this->post('/photos', ['title' => 'Borrar', 'imagen' => UploadedFile::fake()->image('b.jpg')]);
        $photo = Photo::first();

        $this->delete("/photos/{$photo->id}")->assertRedirect('/photos')->assertSessionHas('success');

        $this->assertDatabaseCount('photos', 0);
        Storage::disk('public')->assertMissing($photo->path);
    }
}
