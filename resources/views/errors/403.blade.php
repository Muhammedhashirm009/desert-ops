<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied (403 Forbidden) — DessertOps</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f9fafb;
            --card-bg: #ffffff;
            --txt: #111827;
            --txt2: #4b5563;
            --txt3: #9ca3af;
            --primary: #f59e0b; /* Amber color for 403 authorization error */
            --primary-hov: #d97706;
            --primary-lt: rgba(245, 158, 11, 0.1);
            --border: #e5e7eb;
            --shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.05);
            --radius: 20px;
        }

        body {
            background-color: var(--bg);
            color: var(--txt);
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .error-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 48px 32px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .error-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
        }

        .error-code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 80px;
            font-weight: 800;
            line-height: 1;
            color: var(--primary);
            margin: 0 0 16px;
            letter-spacing: -2px;
            opacity: 0.15;
            position: absolute;
            top: 24px;
            right: 32px;
            user-select: none;
        }

        .illustration {
            width: 120px;
            height: 120px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }

        .error-title {
            font-size: 24px;
            font-weight: 800;
            color: var(--txt);
            margin: 0 0 12px;
            letter-spacing: -0.5px;
        }

        .error-desc {
            font-size: 14.5px;
            color: var(--txt2);
            line-height: 1.6;
            margin: 0 0 32px;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--primary);
            color: #ffffff;
            border-radius: 30px;
            padding: 12px 28px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 4px 6px -1px rgba(245,158,11,0.2), 0 2px 4px -2px rgba(245,158,11,0.2);
            transition: all 0.15s ease;
            cursor: pointer;
        }

        .btn-home:hover {
            background-color: var(--primary-hov);
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(245,158,11,0.25), 0 4px 6px -4px rgba(245,158,11,0.25);
        }

        .btn-home:active {
            transform: translateY(0);
        }

        .footer {
            font-size: 10px;
            color: var(--txt3);
            margin-top: 32px;
            font-family: 'JetBrains Mono', monospace;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">403</div>
        <div class="illustration">
            <svg viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" style="width: 100%; height: 100%;">
                <!-- Cookie Jar body -->
                <path d="M 30 35 C 30 20, 70 20, 70 35 L 75 75 C 75 82, 25 82, 25 75 Z" />
                <!-- Lid -->
                <path d="M 32 25 L 68 25 M 42 25 C 42 18, 58 18, 58 25" />
                <!-- Padlock on the jar -->
                <rect x="40" y="48" width="20" height="16" rx="2" fill="currentColor" opacity="0.2" />
                <rect x="40" y="48" width="20" height="16" rx="2" />
                <path d="M 45 48 V 40 C 45 35, 55 35, 55 40 V 48" />
                <!-- Cookies outline inside jar -->
                <circle cx="38" cy="68" r="8" stroke-dasharray="3,3" />
                <circle cx="62" cy="68" r="8" stroke-dasharray="3,3" />
                <circle cx="50" cy="62" r="8" stroke-dasharray="3,3" />
            </svg>
        </div>
        <h1 class="error-title">Hands Off the Cookie Jar!</h1>
        <p class="error-desc">You don't have secret recipe clearance to access this side of the bakery kitchen. Only authorized chefs can look inside this jar!</p>
        <a href="/" class="btn-home">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Back to Safety
        </a>
        <div class="footer">DessertOps Kitchen Access Control</div>
    </div>
</body>
</html>
