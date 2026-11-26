<?php
use Illuminate\Support\Facades\Route;
use App\Services\PetPlanApi;
Route::view('/', 'portal.comprobacion')->name('inicio');
Route::get('/comprobacion', fn(PetPlanApi $api)=>response()->json($api->request('GET','/comprobacion')))->name('comprobacion');
