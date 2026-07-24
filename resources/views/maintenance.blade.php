<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Update in Progress — PolyCMS</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', sans-serif;
            color: #e2e8f0;
            overflow: hidden;
        }

        .container {
            text-align: center;
            padding: 3rem 2rem;
            max-width: 520px;
            width: 100%;
        }

        .logo-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 2rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 40px rgba(99, 102, 241, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        .logo-icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 40px rgba(99, 102, 241, 0.3); transform: scale(1); }
            50% { box-shadow: 0 0 60px rgba(99, 102, 241, 0.5); transform: scale(1.05); }
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
        }

        p {
            font-size: 1rem;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .progress-bar {
            width: 100%;
            height: 4px;
            background: #1e293b;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .progress-bar-inner {
            height: 100%;
            width: 30%;
            background: linear-gradient(90deg, #6366f1, #a78bfa, #6366f1);
            background-size: 200% 100%;
            border-radius: 2px;
            animation: shimmer 1.5s ease-in-out infinite;
        }

        @keyframes shimmer {
            0% { background-position: 200% 0; width: 20%; }
            50% { width: 60%; }
            100% { background-position: -200% 0; width: 20%; }
        }

        .status {
            font-size: 0.875rem;
            color: #64748b;
        }

        .status span {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dot {
            width: 8px;
            height: 8px;
            background: #6366f1;
            border-radius: 50%;
            animation: blink 1s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .footer {
            margin-top: 3rem;
            font-size: 0.75rem;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
        </div>

        <h1>System Update in Progress</h1>
        <p>PolyCMS is currently being updated to a new version. This process usually takes a few minutes. Please check back shortly.</p>

        <div class="progress-bar">
            <div class="progress-bar-inner"></div>
        </div>

        <div class="status">
            <span>
                <span class="dot"></span>
                Updating core files and database...
            </span>
        </div>

        <div class="footer">
            Powered by PolyCMS
        </div>
    </div>
</body>
</html>
