<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faristol</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0C1934;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            display: flex;
            gap: 2rem;
            padding: 2rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        .card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 1.5rem;
            padding: 3rem 2.5rem;
            width: 320px;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }
        .card:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-4px);
            border-color: rgba(255,255,255,0.25);
        }
        .card h2 {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 600;
        }
        .card p {
            color: rgba(255,255,255,0.6);
            font-size: 0.9rem;
            line-height: 1.4;
        }
        .icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
        }
        .icon-visor { background: linear-gradient(135deg, #667eea, #764ba2); }
        .icon-control { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .icon-admin { background: linear-gradient(135deg, #43e97b, #38f9d7); }
    </style>
</head>
<body>
    <div class="container">
        <a href="/visorweb2/" target="_blank" rel="noopener" class="card">
            <div class="icon icon-visor">♪</div>
            <h2>Faristol App</h2>
            <p>Musical scores viewer and setlist manager for mobile devices</p>
        </a>
        <a href="/control-app/" target="_blank" rel="noopener" class="card">
            <div class="icon icon-control">▷</div>
            <h2>Control App</h2>
            <p>Rehearsal and ensemble management for instructors</p>
        </a>
        <a href="/dashboard" class="card">
            <div class="icon icon-admin">⚙</div>
            <h2>Faristol Admin</h2>
            <p>Platform administration, user management, and content control panel</p>
        </a>
    </div>
</body>
</html>