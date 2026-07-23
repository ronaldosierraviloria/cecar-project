<?php

namespace Tests\Feature;

use App\Http\Controllers\Gestor\TrabajoController;
use App\Models\Retroalimentacion;
use App\Models\Trabajo;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TrabajoControllerTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('historial_estados');
        Schema::dropIfExists('trabajo_profesor');
        Schema::dropIfExists('retroalimentaciones');
        Schema::dropIfExists('trabajo');
        Schema::dropIfExists('usuario');

        Schema::create('usuario', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('nombre');
            $table->string('apellido');
            $table->string('correo')->unique();
            $table->string('password');
            $table->string('rol')->default('Gestor');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('trabajo', function (Blueprint $table) {
            $table->id('id_trabajo');
            $table->string('archivo_pdf')->nullable();
            $table->string('version_actual')->nullable();
            $table->string('estado')->nullable();
            $table->timestamps();
        });

        Schema::create('profesor', function (Blueprint $table) {
            $table->id('id_profesor');
            $table->unsignedBigInteger('id_usuario');
            $table->timestamps();
        });

        Schema::create('retroalimentaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trabajo_grado_id');
            $table->unsignedBigInteger('user_id');
            $table->text('comentario')->nullable();
            $table->string('version_documento')->default('v1');
            $table->timestamps();
        });

        Schema::create('trabajo_profesor', function (Blueprint $table) {
            $table->unsignedBigInteger('id_trabajo');
            $table->unsignedBigInteger('id_profesor');
            $table->timestamp('fecha_asignacion')->nullable();
            $table->timestamp('fecha_limite_revision')->nullable();
            $table->string('estado_revision')->nullable();
            $table->boolean('retroalimentacion_finalizada')->default(false);
            $table->timestamps();
        });

        Schema::create('historial_estados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trabajo_grado_id');
            $table->string('estado');
            $table->string('version_documento')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('observacion_estado')->nullable();
            $table->timestamps();
        });
    }

    public function test_subir_nueva_version_borra_las_retroalimentaciones_existentes(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Gestor',
            'apellido' => 'Prueba',
            'correo' => 'gestor@test.com',
            'password' => bcrypt('password'),
            'rol' => 'Gestor',
            'activo' => true,
        ]);

        $trabajo = Trabajo::create([
            'estado' => 'retroalimentacion_emitida',
            'version_actual' => 'v1',
            'archivo_pdf' => 'storage/pdf/old.pdf',
        ]);

        Retroalimentacion::create([
            'trabajo_grado_id' => $trabajo->id_trabajo,
            'user_id' => $usuario->id_usuario,
            'comentario' => 'Comentario anterior',
            'version_documento' => 'v1',
        ]);

        Route::get('/gestor/trabajos/{id}', function () {
            return 'ok';
        })->name('gestor.trabajo.detalles');

        $this->actingAs($usuario);
        Notification::fake();
        Storage::fake('public');

        $request = new Request();
        $request->files->add([
            'archivo_pdf' => UploadedFile::fake()->create('proyecto.pdf', 100, 'application/pdf'),
        ]);
        $request->merge(['observacion_estado' => 'Nueva revisión para pruebas']);

        $response = (new TrabajoController())->subirNuevaVersion($request, $trabajo->id_trabajo);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(0, Retroalimentacion::where('trabajo_grado_id', $trabajo->id_trabajo)->count());

        $trabajo->refresh();
        $this->assertSame('version_corregida_subida', $trabajo->estado);
        $this->assertSame('v2', $trabajo->version_actual);
    }
}
