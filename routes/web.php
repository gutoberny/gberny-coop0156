<?php

use App\Http\Controllers\SimulacaoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('analise');
});

Route::get('/simulacao/{id}', [SimulacaoController::class, 'show']);

// Diferencial: tela de cadastro/listagem de clientes, consumindo a API REST.
Route::view('/clientes', 'clientes');
