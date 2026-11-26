<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController,PortalController};
use App\Http\Middleware\PortalAuth;
Route::view('/','index')->name('inicio');
Route::view('/login','login')->name('login');
Route::post('/login',[AuthController::class,'login'])->middleware('throttle:10,1')->name('login.attempt');
Route::view('/registro','registro')->name('registro');
Route::post('/registro',[AuthController::class,'registro'])->middleware('throttle:10,1')->name('registro.store');
Route::post('/logout',[AuthController::class,'logout'])->name('logout');
Route::view('/pass_recovery','pass_recovery')->name('pass_recovery');
Route::post('/pass_recovery',[AuthController::class,'recuperar'])->middleware('throttle:5,1')->name('password.send');
Route::get('/pass-reset/{token}',fn($token)=>view('pass-reset',['token'=>$token,'email'=>request('email','')]))->name('password.reset');
Route::post('/pass-reset',[AuthController::class,'reset'])->middleware('throttle:5,1')->name('password.update');
Route::middleware(PortalAuth::class)->group(function(){
 Route::get('/dashboard',[PortalController::class,'dashboard'])->name('dashboard');
 Route::get('/perfil',[PortalController::class,'perfil'])->name('perfil');
 Route::put('/perfil',[PortalController::class,'editarPerfil'])->name('perfil.update');
 Route::put('/password',[PortalController::class,'password'])->name('perfil.password');
});
