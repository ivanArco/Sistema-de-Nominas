<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre_usuario',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'curp',
        'telefono_contacto_1',
        'telefono_contacto_2',
        'fecha_contratacion',
        'area_contratacion',
        'numero_seguro_social',
        'fecha_alta_servicio_salud',
        'direccion',
        'colonia',
        'codigo_postal',
        'ciudad',
        'estado',
        'name',
        'email',
        'password',
        'rol',
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'fecha_contratacion' => 'date',
            'fecha_alta_servicio_salud' => 'date',
            'activo' => 'boolean',
        ];
    }

    public const ROLES_SISTEMA = [
        'EMPLEADO',
        'VENDEDOR',
        'SUPERVISOR',
        'JEFE_AREA',
        'CONTADOR',
        'SECRETARIA',
    ];

    private const MAPA_ROLES_LEGACY = [
        'ADMIN' => 'JEFE_AREA',
        'NOMINISTA' => 'CONTADOR',
        'CONSULTA' => 'EMPLEADO',
    ];

    public static function rolesDisponibles(): array
    {
        return self::ROLES_SISTEMA;
    }

    public static function normalizarRol(?string $rol): string
    {
        $rolNormalizado = strtoupper(trim((string) $rol));

        if (isset(self::MAPA_ROLES_LEGACY[$rolNormalizado])) {
            return self::MAPA_ROLES_LEGACY[$rolNormalizado];
        }

        if (in_array($rolNormalizado, self::ROLES_SISTEMA, true)) {
            return $rolNormalizado;
        }

        return 'EMPLEADO';
    }

    public function rolNormalizado(): string
    {
        return self::normalizarRol((string) $this->rol);
    }

    public function tieneAlgunRol(array $rolesPermitidos): bool
    {
        $rolesNormalizados = array_map(static fn (string $rol) => self::normalizarRol($rol), $rolesPermitidos);

        return in_array($this->rolNormalizado(), $rolesNormalizados, true);
    }
}
