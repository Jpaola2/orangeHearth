<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    protected $table = 'medico_veterinario';
    protected $primaryKey = 'id_mv';
    public $timestamps = false;
    protected $fillable = ['nombre_mv', 'apell_mv', 'cedu_mv', 'tarjeta_profesional_mv', 'user_id', 'especialidad', 'telefono', 'estado'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'id_mv', 'id_mv');
    }
}