<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — DessertOps ERP</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<style>
  body {
    background: var(--pg-bg);
    font-family: 'Inter', -apple-system, sans-serif;
    color: var(--txt);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    margin: 0;
  }

  .login-card {
    background: var(--card);
    border: 1px solid var(--div2);
    border-radius: var(--radius-lg);
    width: 100%;
    max-width: 400px;
    box-shadow: var(--card-sh-md);
    overflow: hidden;
    padding: 32px;
  }

  .login-header {
    text-align: center;
    margin-bottom: 28px;
  }

  .logo-box {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 8px;
    background: var(--purple-tx);
    margin-bottom: 16px;
  }

  .logo-box svg {
    width: 24px;
    height: 24px;
    stroke: #fff;
    fill: none;
    stroke-width: 2.5;
  }

  .login-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--txt);
    letter-spacing: -0.5px;
    margin-bottom: 6px;
  }

  .login-subtitle {
    font-size: 13px;
    color: var(--txt2);
    line-height: 1.5;
  }
</style>
</head>
<body>

<div class="login-card">
  <!-- Card Header -->
  <div class="login-header">
    <div class="logo-box">
      <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
    </div>
    <div class="login-title">DessertOps ERP</div>
    <div class="login-subtitle">Unified authentication gateway for Central Kitchen admin operations and partner store outlets</div>
  </div>

  <!-- Error & Success Alerts -->
  <div style="margin-bottom: 20px;">
    @if(session('success'))
      <div class="alert alert-success" style="margin-bottom: 0; padding: 10px 14px; font-size: 13px;">
        {{ session('success') }}
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger" style="margin-bottom: 0; padding: 10px 14px; font-size: 13px;">
        {{ session('error') }}
      </div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger" style="margin-bottom: 0; padding: 10px 14px; font-size: 13px;">
        {{ $errors->first() }}
      </div>
    @endif
  </div>

  <!-- Form: Combined ERP Login -->
  <form action="{{ route('login.post') }}" method="POST">
    @csrf
    
    <div class="form-grp" style="margin-bottom: 16px;">
      <label for="email" style="display: block; font-weight: 600; font-size: 11px; color: var(--txt2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Email Address</label>
      <input type="email" name="email" id="email" class="form-input" required 
             value="{{ old('email') }}" placeholder="e.g. admin@dessertops.com or mgroad@dessertops.com" style="width: 100%;">
    </div>

    <div class="form-grp" style="margin-bottom: 18px;">
      <label for="password" style="display: block; font-weight: 600; font-size: 11px; color: var(--txt2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Password</label>
      <input type="password" name="password" id="password" class="form-input" required 
             placeholder="••••••••" style="width: 100%;">
    </div>

    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px; color: var(--txt2); margin-bottom: 24px;">
      <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
        <input type="checkbox" name="remember" style="accent-color: var(--purple-tx);">
        <span>Keep me logged in</span>
      </label>
    </div>

    <button type="submit" class="btn-pri" style="width: 100%; justify-content: center; padding: 10px 14px; background: var(--purple-tx); border-color: var(--purple-tx); font-weight: 600; color: #fff;">
      Log In to Dashboard
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 14px; height: 14px; stroke: #fff;"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </button>
  </form>
</div>

</body>
</html>
