<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetPlan | @yield('titulo', 'Panel')</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Rubik:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
</head>
<body class="pp-portal">
@php
    $usuario = session('petplan_usuario', []);
    $rol = $usuario['rol'] ?? '';
    $nombre = $usuario['nombres'] ?? '';
    $apellido = $usuario['apellidos'] ?? '';
    $roles = ['USUARIO'=>'Cliente', 'VETERINARIO'=>'Veterinario', 'ADMINISTRADOR'=>'Administrador', 'SUPERADMINISTRADOR'=>'Superadministrador'];
    $menu = [
        ['dashboard','dashboard','🏠','Inicio',['USUARIO','VETERINARIO','ADMINISTRADOR','SUPERADMINISTRADOR']],
        ['mascotas.index','mascotas.*','🐶','Mis Mascotas',['USUARIO']],
        ['citas.index','citas.*','📅',$rol === 'USUARIO' ? 'Mis Citas' : 'Citas',['USUARIO','VETERINARIO','ADMINISTRADOR']],
        ['historial.index','historial.*','📋','Historial Clínico',['USUARIO']],
        ['veterinarias.index','veterinarias.*','🏥','Veterinarias',['USUARIO','VETERINARIO','ADMINISTRADOR','SUPERADMINISTRADOR']],
        ['cuentas','cuentas','👥','Gestión de Cuentas',['ADMINISTRADOR','SUPERADMINISTRADOR']],
        ['perfil','perfil*','👤','Mi Perfil',['USUARIO','VETERINARIO','ADMINISTRADOR','SUPERADMINISTRADOR']],
    ];
@endphp
<a class="pp-skip" href="#contenido">Ir al contenido</a>
<div class="app-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <a class="logo" href="{{ route('dashboard') }}"><span aria-hidden="true">{{ $rol === 'VETERINARIO' ? '🩺' : '🐾' }}</span><h2>PetPlan</h2></a>
        </div>
        <nav class="sidebar-nav" aria-label="Menú principal">
            <ul>
                @foreach($menu as [$ruta,$patron,$icono,$texto,$permitidos])
                    @if(in_array($rol,$permitidos,true) && \Illuminate\Support\Facades\Route::has($ruta))
                        <li><a href="{{ route($ruta) }}" class="nav-btn {{ request()->routeIs($patron) || ($ruta === 'citas.index' && request()->routeIs('historias.*')) ? 'active' : '' }}" @if(request()->routeIs($patron)) aria-current="page" @endif><span aria-hidden="true">{{ $icono }}</span>{{ $texto }}</a></li>
                    @endif
                @endforeach
            </ul>
        </nav>
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($nombre,0,1).mb_substr($apellido,0,1)) }}</div>
                <div class="user-info"><span class="user-name">{{ trim($nombre.' '.$apellido) }}</span><span class="user-role">{{ $roles[$rol] ?? $rol }}</span></div>
            </div>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="nav-btn pp-logout" type="submit"><span aria-hidden="true">🚪</span>Cerrar Sesión</button></form>
        </div>
    </aside>
    <main class="main-content" id="contenido">
        <header class="topbar"><h2>Bienvenido, {{ $rol === 'VETERINARIO' ? 'Dr(a). ' : '' }}{{ $nombre }} 👋</h2><span class="pp-role-label">{{ $roles[$rol] ?? $rol }}</span></header>
        <div class="views-container pp-main">
            <h1 class="page-title">@yield('titulo', 'Panel')</h1>
            @if(session('status'))<p class="pp-ok" role="status">{{ session('status') }}</p>@endif
            @if($errors->any())<div class="pp-error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('contenido')
        </div>
    </main>
</div>
</body>
</html>
