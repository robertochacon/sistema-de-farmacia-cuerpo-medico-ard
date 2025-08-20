<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mi perfil</title>
  <style>
    :root { --bg:#ffffff; --card:#ffffff; --muted:#64748b; --border:#e5e7eb; --text:#0f172a; --accent:#22c55e; --primary:#1d4ed8; }
    * { box-sizing: border-box; }
    body { margin:0; background:var(--bg); color:var(--text); font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, 'Apple Color Emoji', 'Segoe UI Emoji'; }
    .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
    .topbar { display:flex; align-items:center; gap:12px; margin-bottom: 16px; }
    .back-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 10px; border:1px solid var(--border); border-radius:8px; color:var(--text); text-decoration:none; background:transparent; }
    .back-btn:hover { background:#f8fafc; }
    .title { font-size: 22px; font-weight: 700; }
    .grid { display:grid; grid-template-columns: 1fr; gap: 20px; }
    @media (min-width: 900px) { .grid { grid-template-columns: 1fr 1fr; } }
    .card { background: var(--card); border:1px solid var(--border); border-radius: 14px; padding: 18px; box-shadow: 0 1px 2px rgba(16,24,40,0.06); }
    .card h2 { margin:0 0 12px; font-size: 16px; font-weight: 600; color:#0f172a; }
    .field { margin-bottom: 12px; }
    .label { display:block; margin-bottom:6px; color:#334155; font-size: 13px; }
    .input { width:100%; padding: 10px 12px; border-radius:10px; border:1px solid var(--border); background:#ffffff; color:var(--text); outline:none; }
    .input:focus { border-color:#93c5fd; box-shadow: 0 0 0 3px rgba(59,130,246,0.25); }
    .input-group { position: relative; }
    .toggle { position:absolute; right:8px; top:50%; transform: translateY(-50%); background:transparent; border:none; color:#64748b; cursor:pointer; font-size:12px; padding:4px 6px; border-radius:6px; }
    .toggle:hover { background:#f1f5f9; color:#0f172a; }
    .actions { margin-top: 10px; display:flex; gap:8px; }
    .btn { padding: 9px 12px; border:none; border-radius:10px; cursor:pointer; font-weight:600; }
    .btn-primary { background: var(--primary); color:white; }
    .btn-primary:hover { filter: brightness(1.05); }
    .btn-success { background: var(--accent); color:white; }
    .btn-success:hover { filter: brightness(1.05); }
    .alert { padding:10px 12px; background:#ecfdf5; color:#065f46; border:1px solid #bbf7d0; border-radius:10px; margin-bottom:12px; }
    .error { color:#b91c1c; font-size:12px; margin-top:4px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="topbar">
      <a class="back-btn" href="{{ url('/admin') }}" title="Volver">
        <span>←</span><span>Atrás</span>
      </a>
      <div class="title">Mi perfil</div>
    </div>

    @if(session('status'))
      <div class="alert">{{ session('status') }}</div>
    @endif

    <div class="grid">
      <section class="card">
        <h2>Datos</h2>
        <form method="POST" action="{{ route('profile.update') }}">
          @csrf
          @method('PUT')
          <div class="field">
            <label class="label">Nombre</label>
            <input class="input" type="text" name="name" value="{{ old('name', $user->name) }}" required>
            @error('name')<div class="error">{{ $message }}</div>@enderror
          </div>
          <div class="field">
            <label class="label">Correo</label>
            <input class="input" type="email" name="email" value="{{ old('email', $user->email) }}" required>
            @error('email')<div class="error">{{ $message }}</div>@enderror
          </div>
          <div class="actions">
            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="{{ url('/admin') }}" class="btn" style="background:#334155; color:#e2e8f0;">Cancelar</a>
          </div>
        </form>
      </section>

      <section class="card">
        <h2>Cambiar contraseña</h2>
        <form method="POST" action="{{ route('profile.password') }}">
          @csrf
          @method('PUT')
          <div class="field">
            <label class="label">Contraseña actual</label>
            <div class="input-group">
              <input class="input" id="current_password" type="password" name="current_password" required>
              <button class="toggle" type="button" onclick="togglePassword('current_password', this)">Mostrar</button>
            </div>
            @error('current_password')<div class="error">{{ $message }}</div>@enderror
          </div>
          <div class="field">
            <label class="label">Nueva contraseña</label>
            <div class="input-group">
              <input class="input" id="password" type="password" name="password" required>
              <button class="toggle" type="button" onclick="togglePassword('password', this)">Mostrar</button>
            </div>
            @error('password')<div class="error">{{ $message }}</div>@enderror
          </div>
          <div class="field">
            <label class="label">Confirmar nueva contraseña</label>
            <div class="input-group">
              <input class="input" id="password_confirmation" type="password" name="password_confirmation" required>
              <button class="toggle" type="button" onclick="togglePassword('password_confirmation', this)">Mostrar</button>
            </div>
          </div>
          <div class="actions">
            <button type="submit" class="btn btn-primary">Actualizar contraseña</button>
            <a href="{{ url('/admin') }}" class="btn" style="background:#334155; color:#e2e8f0;">Cancelar</a>
          </div>
        </form>
      </section>
    </div>
  </div>

  <script>
    function togglePassword(id, btn) {
      const input = document.getElementById(id);
      if (!input) return;
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      btn.textContent = isPassword ? 'Ocultar' : 'Mostrar';
    }
  </script>
</body>
</html>
