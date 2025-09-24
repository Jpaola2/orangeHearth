<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $table = 'cita_medica';
    protected $primaryKey = 'id_cita_medi';
    public $timestamps = false;
    protected $fillable = ['fech_cons', 'motiv_cons', 'diag_cons', 'trata_cons', 'id_tutor', 'id_mv', 'id_masc', 'estado'];

    public function tutor()
    {
        return $this->belongsTo(Tutor::class, 'id_tutor', 'id_tutor');
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'id_mv', 'id_mv');
    }

    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_masc', 'id_masc');
    }
}
