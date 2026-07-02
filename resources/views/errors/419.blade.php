<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired (419 Page Expired) — DessertOps</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f9fafb;
            --card-bg: #ffffff;
            --txt: #111827;
            --txt2: #4b5563;
            --txt3: #9ca3af;
            --primary: #8b5cf6; /* Purple color for 419 session expiry error */
            --primary-hov: #7c3aed;
            --primary-lt: rgba(139, 92, 246, 0.1);
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
            background: linear-gradient(90deg, #8b5cf6, #a78bfa);
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
            box-shadow: 0 4px 6px -1px rgba(139,92,246,0.2), 0 2px 4px -2px rgba(139,92,246,0.2);
            transition: all 0.15s ease;
            cursor: pointer;
            border: none;
            outline: none;
            font-family: inherit;
        }

        .btn-home:hover {
            background-color: var(--primary-hov);
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(139,92,246,0.25), 0 4px 6px -4px rgba(139,92,246,0.25);
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
        <div class="error-code">419</div>
        <div class="illustration">
            <svg viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" style="width: 100%; height: 100%;">
                <!-- Clock outer circle -->
                <circle cx="50" cy="45" r="30" />
                <!-- Melting distortion at the bottom -->
                <path d="M 20 45 C 20 65, 30 80, 50 80 C 65 80, 80 65, 80 45" />
                <path d="M 45 80 C 47 88, 53 88, 55 80" />
                <!-- Clock hands melting -->
                <path d="M 50 25 L 50 45 L 68 52" />
                <path d="M 68 52 C 72 55, 70 62, 65 62" />
                <!-- Hour markers -->
                <line x1="50" y1="20" x2="50" y2="24" />
                <line x1="75" y1="45" x2="79" y2="45" />
                <line x1="21" y1="45" x2="25" y2="45" />
            </svg>
        </div>
        <h1 class="error-title">Your Dough Went Stale!</h1>
        <p class="error-desc">This session page has been sitting on the counter for way too long. The baking security tokens have expired. Please refresh the page to get a fresh batch!</p>
        <button onclick="window.location.reload()" class="btn-home">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            Refresh Fresh Batch
        </button>
        <div class="footer">DessertOps Kitchen Security</div>
    </div>
</body>
</html>
