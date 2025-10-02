<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $table = 'cita_medica';
    protected $primaryKey = 'id_cita_medi';
    public $timestamps = false;

    protected $fillable = ['fech_cons', 'hora_cons', 'motiv_cons', 'diag_cons', 'trata_cons', 'id_tutor', 'id_mv', 'id_masc', 'estado'];

    protected $casts = [
        'fech_cons' => 'date',
        'hora_cons' => 'string',
    ];

    public function getFechaHoraAttribute(): Carbon
    {
        $date = $this->fech_cons instanceof Carbon
            ? $this->fech_cons
            : Carbon::parse((string) $this->fech_cons);

        $time = $this->hora_cons ?: '00:00:00';

        return Carbon::parse($date->format('Y-m-d') . ' ' . substr($time, 0, 8));
    }

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
