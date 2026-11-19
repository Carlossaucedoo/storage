<?php

namespace Tests\Feature;

use App\Models\Doc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function pdf(string $nombre = 'doc.pdf', int $kb = 100): UploadedFile
    {
        return UploadedFile::fake()->create($nombre, $kb, 'application/pdf');
    }

    public function test_la_lista_carga(): void
    {
        $this->get('/docs')->assertOk()->assertSee('Gestor de documentos');
    }

    public function test_sube_un_pdf_y_se_abre_en_pestana_nueva(): void
    {
        $this->post('/docs', ['title' => 'Apuntes', 'documento' => $this->pdf()])
            ->assertRedirect('/docs')->assertSessionHas('success');

        $doc = Doc::first();
        $this->assertStringStartsWith('docs/', $doc->path);
        Storage::disk('public')->assertExists($doc->path);

        $this->get('/docs')->assertSee('storage/'.$doc->path)->assertSee('target="_blank"', false);
    }

    public function test_no_acepta_archivos_que_no_son_pdf(): void
    {
        $this->post('/docs', [
            'title' => 'Imagen',
            'documento' => UploadedFile::fake()->image('foto.jpg'),
        ])->assertSessionHasErrors('documento');

        $this->assertDatabaseCount('docs', 0);
    }

    public function test_titulo_y_documento_son_obligatorios(): void
    {
        $this->post('/docs', [])->assertSessionHasErrors(['title', 'documento']);
    }

    public function test_no_acepta_pdfs_de_mas_de_2mb(): void
    {
        $this->post('/docs', ['title' => 'Grande', 'documento' => $this->pdf('grande.pdf', 3000)])
            ->assertSessionHasErrors('documento');
    }

    public function test_reemplazar_borra_el_pdf_antiguo(): void
    {
        $this->post('/docs', ['title' => 'Viejo', 'documento' => $this->pdf('viejo.pdf')]);
        $doc = Doc::first();
        $rutaAntigua = $doc->path;

        $this->put("/docs/{$doc->id}", ['title' => 'Nuevo', 'documento' => $this->pdf('nuevo.pdf')])
            ->assertRedirect('/docs')->assertSessionHas('success');

        $doc->refresh();
        $this->assertEquals('Nuevo', $doc->title);
        Storage::disk('public')->assertMissing($rutaAntigua);
        Storage::disk('public')->assertExists($doc->path);
    }

    public function test_eliminar_borra_registro_y_archivo(): void
    {
        $this->post('/docs', ['title' => 'Borrar', 'documento' => $this->pdf()]);
        $doc = Doc::first();

        $this->delete("/docs/{$doc->id}")->assertRedirect('/docs')->assertSessionHas('success');

        $this->assertDatabaseCount('docs', 0);
        Storage::disk('public')->assertMissing($doc->path);
    }
}
