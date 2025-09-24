<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function index()
    {
        return response('Listado de citas', 200);
    }

    public function create()
    {
        return response('Formulario de creación de cita', 200);
    }

    public function store(Request $request)
    {
        return redirect()->route('tutor.citas.index')->with('ok', 'Cita creada');
    }

    public function show($id)
    {
        return response("Detalle de cita {$id}", 200);
    }

    public function edit($id)
    {
        return response("Editar cita {$id}", 200);
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('tutor.citas.index')->with('ok', 'Cita actualizada');
    }

    public function destroy($id)
    {
        return redirect()->route('tutor.citas.index')->with('ok', 'Cita eliminada');
    }
}

