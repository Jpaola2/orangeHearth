<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriaClinica extends Model
{
    protected $table = 'historia_clinica';
    protected $primaryKey = 'id_hc';
    public $timestamps = false;
    protected $fillable = ['fech_crea_hc', 'obse_gen_masc_hc', 'antec_masc_hc', 'id_masc'];

    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_masc', 'id_masc');
    }
}
