<?php

use App\Http\Controllers\SimulacaoController;
use Illuminate\Support\Facades\Route;

/*
 * As telas consomem a própria API REST via fetch, então as rotas web
 * apenas servem as views — exceto a simulação, que precisa carregar a
 * análise e barrar o acesso quando ela não está aprovada.
 */

Route::redirect('/', '/clientes');

Route::view('/clientes', 'clientes.index');
Route::view('/clientes/novo', 'clientes.create');

Route::view('/solicitacoes', 'solicitacoes.index');
Route::view('/solicitacoes/nova', 'solicitacoes.create');

Route::get('/simulacao/{id}', [SimulacaoController::class, 'show']);
