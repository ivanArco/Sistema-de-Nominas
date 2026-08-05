<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

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
        'rol_id',
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

    private static array $cacheRolIdPorClave = [];

    public static function rolesDisponibles(): array
    {
        if (Schema::hasTable('roles')) {
            $rolesBd = Rol::query()
                ->orderBy('id')
                ->pluck('clave')
                ->map(static fn (string $clave) => self::normalizarRol($clave))
                ->unique()
                ->values()
                ->all();

            if ($rolesBd !== []) {
                return $rolesBd;
            }
        }

        return self::ROLES_SISTEMA;
    }

    public static function resolverRolId(?string $rol): ?int
    {
        if (!Schema::hasTable('roles')) {
            return null;
        }

        $rolNormalizado = self::normalizarRol($rol);

        if (array_key_exists($rolNormalizado, self::$cacheRolIdPorClave)) {
            return self::$cacheRolIdPorClave[$rolNormalizado];
        }

        $rolId = Rol::query()->where('clave', $rolNormalizado)->value('id');
        self::$cacheRolIdPorClave[$rolNormalizado] = $rolId ? (int) $rolId : null;

        return self::$cacheRolIdPorClave[$rolNormalizado];
    }

    public function rolRelacion(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
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
        if (!empty($this->rol_id) && Schema::hasTable('roles')) {
            $claveRol = '';

            if ($this->relationLoaded('rolRelacion')) {
                $claveRol = (string) ($this->rolRelacion?->clave ?? '');
            } else {
                $claveRol = (string) ($this->rolRelacion()->value('clave') ?? '');
            }

            if ($claveRol !== '') {
                return self::normalizarRol($claveRol);
            }
        }

        return self::normalizarRol((string) $this->rol);
    }

    public function tieneAlgunRol(array $rolesPermitidos): bool
    {
        $rolesNormalizados = array_map(static fn (string $rol) => self::normalizarRol($rol), $rolesPermitidos);

        return in_array($this->rolNormalizado(), $rolesNormalizados, true);
    }

    public function tienePermiso(string $permiso): bool
    {
        if (
            empty($this->rol_id)
            || !Schema::hasTable('roles')
            || !Schema::hasTable('permisos')
            || !Schema::hasTable('permiso_rol')
        ) {
            return false;
        }

        return Permiso::query()
            ->whereIn('clave', [$permiso, '*'])
            ->whereHas('roles', fn ($query) => $query->whereKey($this->rol_id))
            ->exists();
    }

    public function esAdministrador(): bool
    {
        $rol = $this->rolNormalizado();
        $area = mb_strtolower(trim((string) ($this->area_contratacion ?? '')));

        return $rol === 'JEFE_AREA' && $area === 'direccion';
    }
}
