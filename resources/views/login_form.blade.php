<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Pto. Diaz Document Tracker</title>
    <link rel="icon" type="image/x-icon" href="{{ asset("assets/img/favicon.png") }}"/>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Archivo:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{
            --tm-primary:#427AB5; --tm-primary-hover:#073879; --tm-secondary:#406AAF;
            --tm-dark:#333; --tm-bg:#F0F2F5; --tm-muted:#7A7777; --tm-danger:#DC3545;
            --tm-font:'Manrope',sans-serif; --tm-head:'Archivo','Manrope',sans-serif;
        }
        *{box-sizing:border-box}
        body{margin:0;font-family:var(--tm-font);background:var(--tm-bg);color:#333;min-height:100vh}
        .tm-login{display:flex;min-height:100vh}

        /* Left brand panel */
        .tm-login-left{
            flex:1 1 55%;position:relative;display:flex;align-items:center;justify-content:center;
            padding:48px;color:#fff;text-align:center;overflow:hidden;
            background:linear-gradient(150deg,#427AB5 0%,#2f5c8f 55%,#073879 100%);
        }
        .tm-login-left::after{content:"";position:absolute;inset:0;background:radial-gradient(circle at 30% 20%,rgba(255,255,255,.12),transparent 45%);}
        .tm-login-left .inner{position:relative;max-width:460px}
        .tm-login-left .seal{width:120px;height:120px;object-fit:contain;margin-bottom:22px;filter:drop-shadow(0 6px 18px rgba(0,0,0,.25))}
        .tm-login-left .sub{font-size:13px;letter-spacing:.08em;text-transform:uppercase;opacity:.85;margin:0}
        .tm-login-left h1{font-family:var(--tm-head);font-weight:800;font-size:34px;line-height:1.1;margin:8px 0 14px}
        .tm-login-left p.tag{font-size:15px;opacity:.9;margin:0}

        /* Right form panel */
        .tm-login-right{flex:1 1 45%;display:flex;align-items:center;justify-content:center;padding:40px 24px}
        .tm-login-card{width:100%;max-width:400px}
        .tm-login-card .brand-sm{display:none;text-align:center;margin-bottom:20px}
        .tm-login-card .brand-sm img{width:70px}
        .tm-login-card h2{font-family:var(--tm-head);font-weight:800;font-size:26px;margin:0 0 6px;color:#1a1919}
        .tm-login-card .lead{color:var(--tm-muted);font-size:14px;margin:0 0 26px}
        .tm-field{margin-bottom:18px}
        .tm-field label{display:block;font-size:12px;font-weight:600;margin-bottom:7px;color:#333}
        .tm-field input{
            width:100%;font-family:var(--tm-font);font-size:14px;color:#333;background:#fff;
            border:1px solid rgba(51,51,51,.2);border-radius:9px;padding:12px 14px;transition:border-color .2s,box-shadow .2s;
        }
        .tm-field input:focus{outline:none;border-color:var(--tm-primary);box-shadow:0 0 0 3px rgba(66,122,181,.14)}
        .tm-remember{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--tm-muted);margin-bottom:22px}
        .tm-remember input{width:16px;height:16px;accent-color:var(--tm-primary)}
        .tm-signin{
            width:100%;border:none;border-radius:9px;padding:13px;background:var(--tm-primary);color:#fff;
            font-family:var(--tm-font);font-weight:700;font-size:14px;letter-spacing:.04em;text-transform:uppercase;
            cursor:pointer;transition:background .25s;
        }
        .tm-signin:hover{background:var(--tm-primary-hover)}
        .tm-alert{
            display:flex;align-items:center;gap:10px;background:rgba(220,53,69,.10);color:var(--tm-danger);
            border:1px solid rgba(220,53,69,.25);border-radius:9px;padding:11px 14px;font-size:13px;font-weight:600;margin-bottom:20px;
        }
        .tm-foot{text-align:center;margin-top:26px;font-size:12px;color:var(--tm-muted)}

        @media (max-width: 900px){
            .tm-login-left{display:none}
            .tm-login-card .brand-sm{display:block}
        }
    </style>
</head>
<body>

    <div class="tm-login">
        <div class="tm-login-left">
            <div class="inner">
                <img src="{{ asset("assets/img/logo.png") }}" alt="Municipality Seal" class="seal">
                <p class="sub">Republic of the Philippines · Municipality of Prieto Diaz</p>
                <h1>Document Tracker System</h1>
                <p class="tag">Managing legislative documents made simple.</p>
            </div>
        </div>

        <div class="tm-login-right">
            <div class="tm-login-card">
                <div class="brand-sm">
                    <img src="{{ asset("assets/img/logo.png") }}" alt="Seal">
                </div>

                <h2>Sign In</h2>
                <p class="lead">Enter your username and password to continue.</p>

                @if(Session::has('error'))
                    <div class="tm-alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        {{ Session::get('error') }}
                    </div>
                @endif

                <form method="post" action="{{ url("/check_login") }}" autocomplete="off">
                    @csrf
                    <div class="tm-field">
                        <label>Username</label>
                        <input type="text" required name="username" placeholder="Enter your username">
                    </div>
                    <div class="tm-field">
                        <label>Password</label>
                        <input type="password" required name="password" placeholder="Enter your password">
                    </div>
                    <label class="tm-remember">
                        <input type="checkbox" id="form-check-default"> Remember me
                    </label>
                    <button type="submit" class="tm-signin">Sign In</button>
                </form>

                <div class="tm-foot">© {{ date('Y') }} Municipality of Prieto Diaz. All rights reserved.</div>
            </div>
        </div>
    </div>

</body>
</html>
