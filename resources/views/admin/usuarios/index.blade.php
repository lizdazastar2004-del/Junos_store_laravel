@extends('layouts.admin')

@section('title', "Gestión de Clientes & Usuarios - Admin Juno's Store")

@section('content')
<!-- Header del módulo -->
<div class="dash-panel" style="margin-bottom: 1.5rem;">
    <div class="panel-header" style="flex-wrap: wrap; gap: 1rem;">
        <div class="panel-title">
            <span class="panel-eyebrow">Comunidad y Cuentas</span>
            <h1>👥 Gestión de Usuarios y Clientes</h1>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn-kawaii-outline">
            <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
        </a>
    </div>
    <p style="color: #695880; font-size: 0.92rem; margin: 0;">
        Revisa los compradores registrados en la plataforma, su actividad de compra y administra el acceso a sus cuentas.
    </p>
</div>

<!-- Filtros de Rol -->
<div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 1.25rem;">
    <a href="{{ route('admin.usuarios.index') }}" 
       class="btn-pill {{ empty($rol) ? 'active' : '' }}" 
       style="{{ empty($rol) ? 'background: #7c5cbf; color: white;' : 'background: white; color: #503d68;' }} border: 1px solid #d5c8ea; padding: 6px 14px; text-decoration: none; border-radius: 999px; font-size: 0.82rem; font-weight: 700;">
        Todos ({{ $totalUsuarios }})
    </a>
    <a href="{{ route('admin.usuarios.index', ['rol' => 'cliente']) }}" 
       class="btn-pill" 
       style="{{ $rol == 'cliente' ? 'background: #9b59b6; color: white;' : 'background: white; color: #9b59b6;' }} border: 1px solid #9b59b666; padding: 6px 14px; text-decoration: none; border-radius: 999px; font-size: 0.82rem; font-weight: 700;">
        Clientes ({{ $totalClientes }})
    </a>
    <a href="{{ route('admin.usuarios.index', ['rol' => 'admin']) }}" 
       class="btn-pill" 
       style="{{ $rol == 'admin' ? 'background: #e84393; color: white;' : 'background: white; color: #e84393;' }} border: 1px solid #e8439366; padding: 6px 14px; text-decoration: none; border-radius: 999px; font-size: 0.82rem; font-weight: 700;">
        Administradores ({{ $totalAdmins }})
    </a>
</div>

<!-- Buscador -->
<div class="dash-panel" style="padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
    <form action="{{ route('admin.usuarios.index') }}" method="GET" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        @if(!empty($rol))
            <input type="hidden" name="rol" value="{{ $rol }}">
        @endif
        <div style="flex: 1; min-width: 240px; position: relative;">
            <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9b84cc;"></i>
            <input type="text" name="buscar" value="{{ $buscar ?? '' }}" placeholder="Buscar por nombre, apellido, correo o teléfono..." 
                   class="form-control" style="padding-left: 38px; border-radius: 999px; border: 1.5px solid #d4c2ea; font-size: 0.88rem;">
        </div>
        <button type="submit" class="btn-kawaii-primary" style="padding: 0.5rem 1.25rem; border-radius: 999px; font-size: 0.88rem;">
            Buscar
        </button>
        @if(!empty($buscar))
            <a href="{{ route('admin.usuarios.index', ['rol' => $rol]) }}" class="btn-kawaii-outline" style="padding: 0.5rem 1rem; border-radius: 999px; font-size: 0.88rem;">
                Limpiar filtro
            </a>
        @endif
    </form>
</div>

<!-- Tabla de Usuarios -->
<div class="dash-panel">
    @if($usuarios->count() > 0)
    <div class="table-responsive">
        <table class="table-kawaii" style="vertical-align: middle;">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Contacto</th>
                    <th>Rol</th>
                    <th style="text-align: center;">Pedidos</th>
                    <th style="text-align: center;">Estado</th>
                    <th style="text-align: center;">Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usuarios as $u)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: {{ $u->id_rol == 1 ? '#ffe3ec' : '#ebdffc' }}; color: {{ $u->id_rol == 1 ? '#e84393' : '#7c5cbf' }}; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1rem;">
                                {{ strtoupper(substr($u->nombre, 0, 1)) }}
                            </div>
                            <div>
                                <strong style="color: #352b43; font-size: 0.9rem; display: block;">{{ $u->nombre_completo }}</strong>
                                <small style="color: #9b84cc;">ID: #{{ $u->id_usuario }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="font-size: 0.85rem; color: #503d68; display: block;">{{ $u->email }}</span>
                        <small style="color: #9b84cc;">{{ $u->telefono ?? 'Sin teléfono registrado' }}</small>
                    </td>
                    <td>
                        @if($u->id_rol == 1)
                            <span class="badge-kawaii badge-kawaii-pink"><i class="fas fa-crown me-1"></i> Admin</span>
                        @else
                            <span class="badge-kawaii badge-kawaii-purple"><i class="fas fa-user me-1"></i> Cliente</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <a href="{{ route('admin.pedidos.index', ['buscar' => $u->email]) }}" class="badge-kawaii badge-kawaii-mint" style="text-decoration: none; font-size: 0.78rem;" title="Ver pedidos de este cliente">
                            {{ $u->pedidos_count }} compras
                        </a>
                    </td>
                    <td style="text-align: center;">
                        @if($u->activo)
                            <span class="badge-kawaii badge-kawaii-mint">Activo</span>
                        @else
                            <span class="badge-kawaii badge-kawaii-pink">Inactivo</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($u->id_usuario !== auth()->id())
                        <form action="{{ route('admin.usuarios.toggle', $u->id_usuario) }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="btn-kawaii-outline" style="padding: 4px 10px; font-size: 0.76rem; border-radius: 8px;">
                                @if($u->activo)
                                    <i class="fas fa-user-slash me-1"></i> Desactivar
                                @else
                                    <i class="fas fa-user-check me-1"></i> Activar
                                @endif
                            </button>
                        </form>
                        @else
                        <span style="font-size: 0.75rem; color: #9b84cc; font-style: italic;">Tu cuenta</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div style="margin-top: 1.5rem; display: flex; justify-content: center;">
        {{ $usuarios->links() }}
    </div>
    @else
    <div class="panel-empty" style="padding: 3rem 1rem;">
        <i class="fas fa-users-slash" style="font-size: 3rem; color: #d8cce8; margin-bottom: 0.75rem;"></i>
        <h4>No se encontraron usuarios</h4>
        <p style="color: #88729f;">Intenta buscar con otros términos.</p>
    </div>
    @endif
</div>
@endsection
