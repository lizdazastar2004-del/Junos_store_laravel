@extends('layouts.app')

@section('title', 'Confirmar Pedido - Juno\'s Store')

@section('content')
<h1 style="font-size:1.6rem;font-weight:800;color:var(--lavanda-dark);margin-bottom:1.5rem;">✅ Confirmar Pedido</h1>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div style="display:grid;grid-template-columns:1fr 360px;gap:2rem;align-items:start;">

    <!-- ══ FORMULARIO ══ -->
    <form method="POST" action="{{ route('pedidos.confirmar') }}">
        @csrf

        <!-- Dirección de entrega -->
        <div style="background:#fff;border-radius:var(--radio);box-shadow:var(--sombra);padding:1.5rem;margin-bottom:1.5rem;">
            <h2 style="font-size:1.1rem;font-weight:800;color:var(--lavanda-dark);margin-bottom:1.25rem;">📍 Dirección de entrega</h2>
            
            @if($direcciones->isNotEmpty())
                <div class="form-group" style="margin-bottom:1.5rem;">
                    <label class="form-label">Usar dirección guardada</label>
                    <select name="direccion_id" class="form-control" id="select-direccion">
                        <option value="">Nueva dirección</option>
                        @foreach($direcciones as $dir)
                            <option value="{{ $dir->id_direccion }}">
                                {{ $dir->calle }}, {{ $dir->ciudad }}, {{ $dir->departamento }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div id="form-direccion">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Calle / Dirección *</label>
                        <input type="text" name="calle" class="form-control" 
                               placeholder="Ej. Calle 10 #45-23" value="{{ old('calle') }}" 
                               {{ $direcciones->isEmpty() ? 'required' : '' }}>
                        @error('calle')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ciudad *</label>
                        <input type="text" name="ciudad" class="form-control" 
                               placeholder="Ej. Popayán" value="{{ old('ciudad') }}"
                               {{ $direcciones->isEmpty() ? 'required' : '' }}>
                        @error('ciudad')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Departamento *</label>
                        <input type="text" name="departamento" class="form-control" 
                               placeholder="Ej. Cauca" value="{{ old('departamento') }}"
                               {{ $direcciones->isEmpty() ? 'required' : '' }}>
                        @error('departamento')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Código postal</label>
                        <input type="text" name="codigo_postal" class="form-control" 
                               placeholder="Ej. 190001" value="{{ old('codigo_postal') }}">
                        @error('codigo_postal')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono *</label>
                        <input type="tel" name="telefono" class="form-control" 
                               placeholder="Ej. 3001234567" value="{{ old('telefono', auth()->user()?->telefono) }}" required>
                        @error('telefono')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Método de pago -->
        <div style="background:#fff;border-radius:var(--radio);box-shadow:var(--sombra);padding:1.5rem;margin-bottom:1.5rem;">
            <h2 style="font-size:1.1rem;font-weight:800;color:var(--lavanda-dark);margin-bottom:1.25rem;">💳 Método de pago</h2>
            <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                @php
                $metodos = [
                    ['valor'=>'transferencia','emoji'=>'🏦','label'=>'Transferencia bancaria'],
                    ['valor'=>'efectivo','emoji'=>'💵','label'=>'Efectivo (contra entrega)'],
                    ['valor'=>'tarjeta','emoji'=>'💳','label'=>'Tarjeta de crédito/débito'],
                ];
                @endphp
                @foreach($metodos as $m)
                <label style="flex:1;min-width:140px;cursor:pointer;">
                    <input type="radio" name="metodo_pago" value="{{ $m['valor'] }}" required
                           style="position:absolute;opacity:0;pointer-events:none;" class="radio-pago"
                           {{ old('metodo_pago') === $m['valor'] ? 'checked' : '' }}>
                    <div class="tarjeta-pago" data-val="{{ $m['valor'] }}"
                         style="border:2px solid var(--lila);border-radius:var(--radio);padding:1rem;text-align:center;transition:.2s;">
                        <div style="font-size:2rem;margin-bottom:.4rem;">{{ $m['emoji'] }}</div>
                        <div style="font-size:.85rem;font-weight:700;">{{ $m['label'] }}</div>
                    </div>
                </label>
                @endforeach
            </div>
            @error('metodo_pago')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Notas adicionales -->
        <div style="background:#fff;border-radius:var(--radio);box-shadow:var(--sombra);padding:1.5rem;">
            <h2 style="font-size:1.1rem;font-weight:800;color:var(--lavanda-dark);margin-bottom:1.25rem;">📝 Notas adicionales (opcional)</h2>
            <textarea name="notas" class="form-control" rows="3" 
                      placeholder="Instrucciones especiales de entrega, referencias, etc.">{{ old('notas') }}</textarea>
        </div>

        <button type="submit" class="btn-primary" style="margin-top:1.5rem;width:100%;justify-content:center;display:flex;">
            ✅ Confirmar Pedido
        </button>
    </form>

    <!-- ══ RESUMEN ══ -->
    <div style="background:#fff;border-radius:var(--radio);box-shadow:var(--sombra);padding:1.5rem;position:sticky;top:90px;">
        <h2 style="font-size:1.1rem;font-weight:800;color:var(--lavanda-dark);margin-bottom:1.25rem;">Resumen</h2>
        @foreach($items as $item)
        <div style="display:flex;justify-content:space-between;font-size:.88rem;margin-bottom:.4rem;">
            <span>{{ $item->producto->nombre }} ×{{ $item->cantidad }}</span>
            <span>${{ number_format($item->calcularSubtotal(), 0, ',', '.') }}</span>
        </div>
        @endforeach
        <hr style="border:none;border-top:1px solid var(--lila);margin:.75rem 0;">
        <div style="display:flex;justify-content:space-between;font-size:.9rem;margin-bottom:.4rem;">
            <span>Subtotal</span><span>${{ number_format($subtotal, 0, ',', '.') }}</span>
        </div>
        @if($descuento > 0)
        <div style="display:flex;justify-content:space-between;font-size:.9rem;margin-bottom:.4rem;color:#27ae60;">
            <span>Descuento</span><span>−${{ number_format($descuento, 0, ',', '.') }}</span>
        </div>
        @endif
        <div style="display:flex;justify-content:space-between;font-size:.9rem;margin-bottom:.4rem;">
            <span>Envío</span>
            <span>
                @if($envio > 0)
                    ${{ number_format($envio, 0, ',', '.') }}
                @else
                    <span style="color:#27ae60;">Gratis</span>
                @endif
            </span>
        </div>
        <hr style="border:none;border-top:1px solid var(--lila);margin:.75rem 0;">
        <div style="display:flex;justify-content:space-between;font-size:1.1rem;font-weight:800;color:var(--lavanda-dark);">
            <span>Total</span><span>${{ number_format($total, 0, ',', '.') }}</span>
        </div>
    </div>

</div>

<script>
// Manejo de selección de dirección guardada
document.getElementById('select-direccion')?.addEventListener('change', function() {
    const formDireccion = document.getElementById('form-direccion');
    const inputs = formDireccion.querySelectorAll('input');
    
    if (this.value) {
        // Si selecciona dirección guardada, deshabilitar campos
        inputs.forEach(input => {
            input.removeAttribute('required');
            input.disabled = true;
        });
        formDireccion.style.opacity = '0.5';
    } else {
        // Si selecciona "Nueva dirección", habilitar campos
        inputs.forEach(input => {
            if (input.name !== 'codigo_postal') {
                input.setAttribute('required', 'required');
            }
            input.disabled = false;
        });
        formDireccion.style.opacity = '1';
    }
});

// Manejo de selección de método de pago
document.querySelectorAll('.radio-pago').forEach(function(radio){
    radio.addEventListener('change', function(){
        document.querySelectorAll('.tarjeta-pago').forEach(function(t){
            t.style.borderColor = 'var(--lila)';
            t.style.background  = '#fff';
        });
        var tarjeta = document.querySelector('.tarjeta-pago[data-val="'+this.value+'"]');
        if(tarjeta){ 
            tarjeta.style.borderColor='var(--lavanda-dark)'; 
            tarjeta.style.background='#f8f5ff'; 
        }
    });
    
    // Aplicar estilo al método ya seleccionado
    if (radio.checked) {
        radio.dispatchEvent(new Event('change'));
    }
});

document.querySelectorAll('.tarjeta-pago').forEach(function(t){
    t.addEventListener('click', function(){
        var radio = document.querySelector('input[value="'+this.dataset.val+'"]');
        if(radio){ radio.checked=true; radio.dispatchEvent(new Event('change')); }
    });
});
</script>
@endsection
