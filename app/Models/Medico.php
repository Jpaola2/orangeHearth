<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Cita;

class Medico extends Model
{
    protected $table = 'medico_veterinario';
    protected $primaryKey = 'id_mv';
    public $timestamps = false;

    protected $fillable = [
        'nombre_mv',
        'apell_mv',
        'cedu_mv',
        'tarjeta_profesional_mv',
        'user_id',
        'especialidad',
        'telefono',
    ];

    // ✅ especifica la FK para evitar ambigüedad
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Si en la tabla 'citas' la columna que referencia al médico se llama id_mv, esto está OK.
    public function citas()
    {
        return $this->hasMany(Cita::class, 'id_mv', 'id_mv');
    }
}
