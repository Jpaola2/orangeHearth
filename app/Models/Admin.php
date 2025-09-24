<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'administrador';
    protected $primaryKey = 'cod_adm';
    public $timestamps = false;

    protected $fillable = [
        // 'ced_adm' eliminado: ya no se usa NIT/cedula en el modelo
        'nom_adm',
        'apell_adm',
        'email',
        'password',
        'id_clinica',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

