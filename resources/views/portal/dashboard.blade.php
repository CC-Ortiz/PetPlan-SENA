@extends('layouts.portal')
@section('titulo', session('petplan_usuario.rol') === 'USUARIO' ? 'Resumen de tus Mascotas' : 'Resumen General')
@section('contenido')
    @if(!empty($avisos))
        <div class="pp-notice" role="status">@foreach($avisos as $aviso)<p>{{ $aviso }}</p>@endforeach</div>
    @endif
    @if(count($estadisticas))
    <div class="stats-grid">
        @foreach($estadisticas as $stat)
            <a class="stat-card" href="{{ route($stat['ruta']) }}">
                <div class="stat-icon {{ $stat['color'] }}" aria-hidden="true">{{ $stat['icono'] }}</div>
                <div class="stat-details"><h3>{{ $stat['titulo'] }}</h3><p class="stat-number">{{ $stat['total'] ?? '—' }}</p></div>
            </a>
        @endforeach
    </div>
    @endif
    @if($citas !== null)
        <section class="card pp-dashboard-card" aria-labelledby="proximas-citas">
            <div class="card-header pp-section-header"><h2 id="proximas-citas">Próximas Citas</h2><a class="pp-link" href="{{ route('citas.index') }}">Ver todas</a></div>
            <ul class="activity-list">
                @forelse($citas as $cita)
                    <li><div class="activity-icon" aria-hidden="true">📅</div><div class="activity-text"><p><strong>{{ $cita['tipo_cita'] }}</strong> · {{ $cita['veterinaria'] }}</p><span class="time">{{ $cita['fecha_panel'] }}</span></div><span class="pp-status {{ $cita['confirmacion'] ? 'is-confirmed' : 'is-pending' }}">{{ $cita['confirmacion'] ? 'Confirmada' : 'Pendiente' }}</span></li>
                @empty
                    <li><p class="pp-muted">No hay citas próximas.</p></li>
                @endforelse
            </ul>
        </section>
    @endif
    @if($clinicas !== null && session('petplan_usuario.rol') === 'SUPERADMINISTRADOR')
        <section class="card pp-dashboard-card" aria-labelledby="pendientes-clinicas">
            <div class="card-header pp-section-header"><h2 id="pendientes-clinicas">Veterinarias por Aprobar</h2><a class="pp-link" href="{{ route('veterinarias.index') }}">Gestionar</a></div>
            <ul class="activity-list">
                @forelse($clinicas as $clinica)
                    <li><div class="activity-icon" aria-hidden="true">🏥</div><div class="activity-text"><p><strong>{{ $clinica['nombre'] }}</strong></p><span class="time">NIT: {{ $clinica['nit'] }}</span></div><span class="pp-status is-pending">Pendiente</span></li>
                @empty<li><p class="pp-muted">No hay veterinarias pendientes de aprobación.</p></li>@endforelse
            </ul>
        </section>
    @endif
    @if(!count($estadisticas) && empty($avisos))
        <section class="card pp-dashboard-card"><h2>Tu panel de PetPlan</h2><p>Selecciona una opción disponible para gestionar tu información.</p></section>
    @endif
    <section class="card pp-dashboard-card" aria-labelledby="accesos">
        <div class="card-header"><h2 id="accesos">Accesos Rápidos</h2></div>
        <div class="pp-quick-grid">
            @foreach($accesos as $acceso)
                <a class="pp-quick" href="{{ route($acceso['ruta']) }}"><span class="activity-icon" aria-hidden="true">{{ $acceso['icono'] }}</span><span><strong>{{ $acceso['titulo'] }}</strong><small>{{ $acceso['descripcion'] }}</small></span><span aria-hidden="true">›</span></a>
            @endforeach
        </div>
    </section>
@endsection
