@extends('layouts.app')

@section('titulo', 'Registro')

@section('nav-links')
    <li> <a href="{{ route('login') }}" class="btn btn-primary"> Iniciar Sesión </a> </li>
    <li><a href="{{ route('inicio') }}"> Inicio </a></li>
@endsection

@section('contenido')
<section class="register-section">
    <div class="register-container">
        <h1> Crear Cuenta </h1>

        @if ($errors->any())
            <div class="badge danger" style="display:block; margin-bottom: 16px; padding: 12px;">
                <ul style="margin:0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="register-grid">

<!-- Registro de Usuario (Dueño de la mascota) -->
            <div class="register-card">
                <div class="register-icon"> 🐾 </div>
                    <form action="{{ route('registro.store') }}" method="POST">
                        @csrf
                        <div class="form-grupo">
                            <label>Nombre</label>
                            <input type="text" name="nombres" value="{{ old('nombres') }}" placeholder="Nombre" required>
                        </div>

                        <div class="form-grupo">
                            <label>Apellido</label>
                            <input type="text" name="apellidos" value="{{ old('apellidos') }}" placeholder="Apellido" required>
                        </div>

                        <div class="form-grupo">
                            <label>Correo Electrónico</label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="correo@ejemplo.com">
                        </div>

                        <div class="form-grupo">
                            <label>Teléfono</label>
                            <input type="text" name="numero_contacto" value="{{ old('numero_contacto') }}" required placeholder="Número de teléfono">
                        </div>

                        

                        <div class="form-grupo">
                            <label>Número de Documento</label>
                            <input type="text" name="numero_documento" value="{{ old('numero_documento') }}" required placeholder="Número de documento">
                        </div>

                        <div class="form-grupo"><label for="user">Nombre de usuario</label><input id="user" type="text" name="user" value="{{ old('user') }}" required maxlength="50"></div>
                        <div class="form-grupo">
                            <label>Contraseña</label>
                            <input type="password" name="password" required placeholder="********">
                        </div>

                        <div class="form-grupo">
                            <label>Confirmar Contraseña</label>
                            <input type="password" name="password_confirmation" required placeholder="********">
                        </div>

                        <button class="btn btn-primary register-btn"> Registrarme </button>
                    </form>
            </div>
        </div>
    </div>
</section>
@endsection
