<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutor extends Model
{
    protected $table = 'tutor';
    protected $primaryKey = 'id_tutor';
    public $timestamps = false;
    protected $fillable = ['ced_tutor', 'nomb_tutor', 'apell_tutor', 'tel_tutor', 'correo_tutor', 'direc_tutor', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mascotas()
    {
        return $this->hasMany(Mascota::class, 'id_tutor', 'id_tutor');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'id_tutor', 'id_tutor');
    }
}
