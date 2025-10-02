<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Model
{
    use HasFactory;

    protected $table = 'admins';

    protected $fillable = [
        'user_id',
        'nombre_completo',
        'email',
        'telefono',
        'cedula',
        'empresa_nombre',
        'nit',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

