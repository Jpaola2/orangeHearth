<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mascota extends Model
{
    protected $table = 'mascota';
    protected $primaryKey = 'id_masc';
    public $timestamps = false;
    protected $fillable = ['nom_masc', 'espe_masc', 'gene_masc', 'id_tutor'];

    public function tutor()
    {
        return $this->belongsTo(Tutor::class, 'id_tutor', 'id_tutor');
    }

    public function historias()
    {
        return $this->hasMany(HistoriaClinica::class, 'id_masc', 'id_masc');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'id_masc', 'id_masc');
    }
}
