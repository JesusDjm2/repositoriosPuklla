@php
    $selectedRoles = old('roles', $proyecto?->roles->pluck('id')->all() ?? []);
@endphp

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="nombre">Nombre:</label>
        <input id="nombre" type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
            value="{{ old('nombre', $proyecto->nombre ?? '') }}" required>
        @error('nombre')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-group col-md-6">
        <label for="slug">Slug (identificador único):</label>
        <input id="slug" type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
            value="{{ old('slug', $proyecto->slug ?? '') }}" required>
        <small class="form-text text-muted">Identifica este proyecto internamente en el código (íconos, contadores,
            etc). Obligatorio y único.</small>
        @error('slug')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="descripcion">Descripción:</label>
    <textarea id="descripcion" name="descripcion" rows="2" class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $proyecto->descripcion ?? '') }}</textarea>
    @error('descripcion')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="color">Color de marca:</label>
        <input id="color" type="color" name="color" class="form-control @error('color') is-invalid @enderror"
            value="{{ old('color', $proyecto->color ?? '#6a0310') }}">
        @error('color')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-group col-md-6">
        <label for="orden">Orden:</label>
        <input id="orden" type="number" min="0" name="orden"
            class="form-control @error('orden') is-invalid @enderror"
            value="{{ old('orden', $proyecto->orden ?? 0) }}">
        @error('orden')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="logo">Logo:</label>
        <input id="logo" type="file" name="logo" class="form-control-file @error('logo') is-invalid @enderror"
            accept="image/png,image/jpeg,image/webp">
        @if (($proyecto->logo ?? null))
            <div class="mt-2">
                <img src="{{ $proyecto->logoUrl() }}" width="48" height="48" style="object-fit:contain;">
            </div>
        @endif
        @error('logo')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-group col-md-6 d-flex align-items-center">
        <div class="form-check mt-4">
            <input type="hidden" name="activo" value="0">
            <input id="activo" type="checkbox" name="activo" value="1" class="form-check-input"
                {{ old('activo', $proyecto->activo ?? true) ? 'checked' : '' }}>
            <label for="activo" class="form-check-label">Proyecto activo (visible en el hub público)</label>
        </div>
    </div>
</div>

<div class="form-group">
    <label>Roles asociados a este proyecto:</label>
    <div class="d-flex flex-wrap" style="gap:.75rem;">
        @foreach ($roles as $role)
            <div class="form-check">
                <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                    id="role-{{ $role->id }}" class="form-check-input"
                    {{ in_array($role->id, $selectedRoles) ? 'checked' : '' }}>
                <label for="role-{{ $role->id }}" class="form-check-label">{{ $role->name }}</label>
            </div>
        @endforeach
    </div>
    @error('roles')
        <span class="text-danger small">{{ $message }}</span>
    @enderror
</div>
