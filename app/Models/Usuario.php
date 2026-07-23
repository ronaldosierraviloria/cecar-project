<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// Importamos los modelos necesarios para las relaciones:
use App\Models\Trabajo; 
use App\Models\TrabajoProfesor; // Asumimos que este modelo existe para el pivote

class Usuario extends Authenticatable
{
    use Notifiable;

    // --- CORRECCIONES BÁSICAS DE CONVENCIÓN ---
    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nombre',
        'apellido',
        'correo',
        'password',
        'rol',
        'activo',
        'id_facultad',
    ];

    protected $hidden = [
        'password',
    ];

    // Renombramos el campo de password para usar 'contraseña'
    protected $casts = [
        'password' => 'hashed',
        'activo' => 'boolean',
    ];


    // --- AJUSTES DE AUTENTICACIÓN ---

    /**
     * Define la columna utilizada como identificador único de la sesión (ej: 'id_usuario').
     * @return string
     */
    public function getAuthIdentifierName()
    {
        // Esto define qué columna usar para buscar al usuario por ID después del login
        return 'id_usuario'; 
    }
    
    /**
     * Define la columna que contiene el valor del identificador de usuario para el login.
     * Si quieres iniciar sesión por CORREO, necesitas definir esto.
     * @return string
     */
    // NOTA: Laravel por defecto usa 'email'. Si quieres usar 'correo', 
    // debes configurarlo en el config/auth.php o sobrescribir el campo en el login.
    
    /**
     * Define la columna de la contraseña.
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }
    
    /**
     * Define el campo de contraseña para el login.
     * @return string
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    public function profesor()
    {
    // Relación Uno a Uno: Un Usuario puede ser un Profesor
    return $this->hasOne(Profesor::class, 'id_usuario', 'id_usuario');
    }

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'id_facultad', 'id_facultad');
    }
    // --- RELACIONES ---

    /**
     * Relación Muchos a Muchos con Trabajos asignados al Usuario (Evaluador/Profesor).
     */
    public function trabajosAsignados()
    {
        // La clave local es id_usuario (implícito por $primaryKey)
        // La clave foránea en la tabla pivote es id_profesor
     return $this->belongsToMany(Trabajo::class, 'trabajo_profesor', 'id_profesor', 'id_trabajo')
                 ->using(TrabajoProfesor::class) // Usamos el modelo pivote personalizado
                 ->withPivot('fecha_asignacion', 'fecha_limite', 'estado_revision') // Asumiendo 'fecha_limite'
                 ->withTimestamps(); 


    }

    public function retroalimentaciones()
    {
        return $this->hasMany(Retroalimentacion::class, 'user_id', 'id_usuario');
    }
}