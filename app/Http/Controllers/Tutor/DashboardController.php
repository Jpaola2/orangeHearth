<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tutor = Tutor::with('mascotas')
            ->where('user_id', optional($user)->id)
            ->first();

        $mascotas = $tutor ? $tutor->mascotas : collect();

        return view('dashboards.tutor', [
            'mascotas' => $mascotas,
            'tutor' => $tutor,
        ]);
    }
}

