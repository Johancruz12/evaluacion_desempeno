<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión — Clínica Junical</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900&family=poppins:300,400,500,600,700,800" rel="stylesheet"/>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Poppins','Inter',system-ui,sans-serif;overflow:hidden;height:100vh}

        /* ═══ ANIMATED BACKGROUND ═══ */
        .bg-scene{
            position:fixed;inset:0;z-index:0;
            background:linear-gradient(160deg,#e8f4fd 0%,#f0f8ff 20%,#ffffff 40%,#eef7fc 60%,#dceefb 80%,#e3f1fa 100%);
            overflow:hidden;
        }

        /* Flowing light streaks */
        .light-streak{
            position:absolute;
            background:linear-gradient(90deg,transparent,rgba(120,200,240,.12),rgba(180,225,250,.18),transparent);
            height:2px;border-radius:50%;
            animation:streak var(--dur) var(--delay) ease-in-out infinite;
        }
        @keyframes streak{
            0%{transform:translateX(-100%) scaleX(.3);opacity:0}
            30%{opacity:1}
            70%{opacity:1}
            100%{transform:translateX(200vw) scaleX(1.5);opacity:0}
        }

        /* Soft aurora waves */
        .aurora{
            position:absolute;border-radius:50%;filter:blur(80px);opacity:.4;
            animation:auroraMove var(--dur) ease-in-out infinite alternate;
        }
        @keyframes auroraMove{
            0%{transform:translate(0,0) scale(1)}
            100%{transform:translate(var(--tx),var(--ty)) scale(var(--sc))}
        }

        /* Floating particles */
        .sparkle{
            position:absolute;width:var(--s);height:var(--s);
            background:radial-gradient(circle,rgba(100,180,240,.5),transparent 70%);
            border-radius:50%;pointer-events:none;
            animation:sparkleFloat var(--dur) var(--delay) ease-in-out infinite;
        }
        @keyframes sparkleFloat{
            0%,100%{transform:translateY(0) scale(1);opacity:.3}
            50%{transform:translateY(var(--ty)) scale(1.3);opacity:.7}
        }

        /* Bottom light flare */
        .flare{
            position:absolute;bottom:-80px;right:-40px;
            width:500px;height:300px;
            background:radial-gradient(ellipse,rgba(100,200,255,.15),rgba(150,220,250,.08),transparent 70%);
            animation:flareGlow 6s ease-in-out infinite alternate;
        }
        @keyframes flareGlow{0%{opacity:.5;transform:scale(1)}100%{opacity:.8;transform:scale(1.15)}}

        /* Top right subtle glow */
        .glow-tr{
            position:absolute;top:-100px;right:-60px;
            width:400px;height:400px;
            background:radial-gradient(circle,rgba(180,220,255,.2),transparent 65%);
            animation:glowPulse 8s ease-in-out infinite alternate;
        }
        @keyframes glowPulse{0%{opacity:.4;transform:scale(.9)}100%{opacity:.7;transform:scale(1.1)}}

        /* ═══ GLASSMORPHISM CARD ═══ */
        .glass-card{
            position:relative;z-index:10;
            width:100%;max-width:420px;
            background:rgba(255,255,255,.55);
            backdrop-filter:blur(24px) saturate(1.4);
            -webkit-backdrop-filter:blur(24px) saturate(1.4);
            border-radius:24px;
            border:1.5px solid rgba(255,255,255,.7);
            box-shadow:
                0 8px 32px rgba(100,160,220,.12),
                0 2px 8px rgba(100,160,220,.06),
                inset 0 1px 0 rgba(255,255,255,.8),
                inset 0 -1px 0 rgba(200,220,240,.2);
            overflow:hidden;
            animation:cardAppear .7s cubic-bezier(.22,1,.36,1) both;
        }
        @keyframes cardAppear{from{opacity:0;transform:translateY(30px) scale(.96)}to{opacity:1;transform:translateY(0) scale(1)}}

        /* Glass shimmer border effect */
        .glass-card::before{
            content:'';position:absolute;inset:-1px;
            border-radius:25px;padding:1.5px;
            background:linear-gradient(135deg,rgba(255,255,255,.6),rgba(200,225,255,.3),rgba(255,255,255,.1),rgba(180,215,250,.4));
            -webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);
            -webkit-mask-composite:xor;mask-composite:exclude;
            pointer-events:none;
        }

        /* ═══ CARD CONTENT ═══ */
        .card-body{padding:2.5rem 2.2rem 2rem;position:relative}
        @media(min-width:480px){.card-body{padding:2.5rem 2.8rem 2rem}}

        /* Logo */
        .logo-wrap{
            width:170px;height:170px;margin:0 auto 1.8rem;
            background:rgba(255,255,255,.95);
            border-radius:32px;
            display:flex;align-items:center;justify-content:center;
            padding:22px;
            box-shadow:0 12px 40px rgba(100,170,230,.25),inset 0 2px 0 rgba(255,255,255,1),0 0 60px rgba(100,180,240,.08);
            animation:logoIn .7s .2s cubic-bezier(.34,1.56,.64,1) both;
            transition:transform .4s ease;
            position:relative;z-index:5;
        }
        .logo-wrap:hover{transform:scale(1.08) rotate(-3deg)}
        @keyframes logoIn{from{opacity:0;transform:scale(.6) rotate(8deg)}to{opacity:1;transform:scale(1) rotate(0)}}
        .logo-wrap img{width:100%;height:100%;object-fit:contain}

        /* Animated rings behind logo */
        .logo-halo{
            position:absolute;left:50%;top:0;transform:translateX(-50%);
            width:170px;height:170px;z-index:3;
        }
        .ring{
            position:absolute;inset:0;border-radius:50%;
            border:2px solid rgba(100,180,240,.2);
            animation:ringPulse var(--dur) ease-in-out infinite;
        }
        @keyframes ringPulse{
            0%{transform:scale(1);opacity:.3}
            50%{transform:scale(1.3);opacity:.05}
            100%{transform:scale(1);opacity:.3}
        }
        .ring-1{--dur:2.5s;animation-delay:0s}
        .ring-2{--dur:2.5s;animation-delay:.4s;border-color:rgba(120,200,250,.15)}
        .ring-3{--dur:2.5s;animation-delay:.8s;border-color:rgba(80,160,230,.1)}

        /* Orbiting dots */
        .orbit-container{
            position:absolute;left:50%;top:0;transform:translateX(-50%);
            width:200px;height:200px;z-index:2;
        }
        .orbit-dot{
            position:absolute;width:7px;height:7px;
            background:rgba(80,180,240,.5);
            border-radius:50%;
            animation:orbit var(--dur) linear infinite;
            box-shadow:0 0 10px rgba(80,180,240,.3);
        }
        @keyframes orbit{
            0%{transform:rotate(0deg) translateX(100px) rotate(0deg)}
            100%{transform:rotate(360deg) translateX(100px) rotate(-360deg)}
        }
        .orbit-dot-1{--dur:7s;animation-delay:0s;top:50%;left:50%;margin:-3.5px 0 0 -3.5px}
        .orbit-dot-2{--dur:9s;animation-delay:-2s;top:50%;left:50%;margin:-3.5px 0 0 -3.5px;background:rgba(100,200,250,.45)}
        .orbit-dot-3{--dur:11s;animation-delay:-4s;top:50%;left:50%;margin:-3.5px 0 0 -3.5px;background:rgba(120,210,255,.4);width:6px;height:6px;margin:-3px 0 0 -3px}
        .orbit-dot-4{--dur:10s;animation-delay:-6s;top:50%;left:50%;margin:-3px 0 0 -3px;background:rgba(90,190,245,.35);width:5px;height:5px;margin:-2.5px 0 0 -2.5px}

        /* Floating shapes behind logo */
        .logo-bg-shape{
            position:absolute;z-index:1;border-radius:50%;
            animation:shapeFloat var(--dur) ease-in-out infinite;
        }
        @keyframes shapeFloat{
            0%,100%{transform:translate(0,0) scale(1)}
            50%{transform:translate(var(--tx),var(--ty)) scale(var(--sc))}
        }
        .shape-1{
            width:70px;height:70px;
            background:radial-gradient(circle,rgba(120,200,250,.18),transparent 70%);
            left:calc(50% - 110px);top:30px;
            --dur:6s;--tx:12px;--ty:-10px;--sc:1.15;
        }
        .shape-2{
            width:60px;height:60px;
            background:radial-gradient(circle,rgba(100,180,240,.15),transparent 70%);
            left:calc(50% + 80px);top:20px;
            --dur:7s;--tx:-10px;--ty:12px;--sc:1.2;
        }
        .shape-3{
            width:50px;height:50px;
            background:radial-gradient(circle,rgba(150,220,255,.12),transparent 70%);
            left:calc(50% - 90px);top:120px;
            --dur:8s;--tx:8px;--ty:-8px;--sc:1.1;
        }

        /* ═══ ANIMATED RINGS IN BACKGROUND ═══ */
        .bg-rings{
            position:absolute;inset:0;z-index:1;overflow:hidden;pointer-events:none;
        }
        .bg-ring{
            position:absolute;border-radius:50%;
            border:1px solid rgba(100,180,240,.15);
            animation:bgRingExpand var(--dur) ease-in-out infinite;
        }
        @keyframes bgRingExpand{
            0%{transform:translate(-50%,-50%) scale(0);opacity:0}
            20%{opacity:.4}
            80%{opacity:.1}
            100%{transform:translate(-50%,-50%) scale(var(--scale));opacity:0}
        }
        .bg-ring-1{width:300px;height:300px;left:20%;top:30%;--dur:8s;--scale:2.5;animation-delay:0s}
        .bg-ring-2{width:250px;height:250px;right:15%;top:60%;--dur:10s;--scale:2.8;animation-delay:-2s;border-color:rgba(120,200,250,.12)}
        .bg-ring-3{width:350px;height:350px;left:60%;top:15%;--dur:12s;--scale:2.3;animation-delay:-4s;border-color:rgba(80,160,230,.1)}
        .bg-ring-4{width:280px;height:280px;left:10%;bottom:20%;--dur:9s;--scale:2.6;animation-delay:-6s;border-color:rgba(100,190,245,.13)}
        .bg-ring-5{width:200px;height:200px;right:25%;bottom:35%;--dur:11s;--scale:3;animation-delay:-3s;border-color:rgba(110,195,248,.11)}
        
        /* Floating subtle rings */
        .float-ring{
            position:absolute;border-radius:50%;
            border:2px solid rgba(100,180,240,.08);
            animation:floatRingMove var(--dur) ease-in-out infinite;
            pointer-events:none;
        }
        @keyframes floatRingMove{
            0%,100%{transform:translate(0,0) scale(1);opacity:.3}
            50%{transform:translate(var(--tx),var(--ty)) scale(1.1);opacity:.6}
        }
        .float-ring-1{width:150px;height:150px;left:5%;top:10%;--dur:7s;--tx:15px;--ty:-10px}
        .float-ring-2{width:120px;height:120px;right:8%;top:25%;--dur:9s;--tx:-12px;--ty:15px;border-color:rgba(120,200,250,.06)}
        .float-ring-3{width:180px;height:180px;left:15%;bottom:15%;--dur:10s;--tx:10px;--ty:12px;border-color:rgba(80,160,230,.05)}
        .float-ring-4{width:100px;height:100px;right:18%;bottom:28%;--dur:8s;--tx:-8px;--ty:-10px;border-color:rgba(90,175,235,.07)}

        /* Title */
        .card-title{
            text-align:center;margin-bottom:.4rem;
            font-size:1.55rem;font-weight:700;color:#1e3a5f;
            letter-spacing:-.02em;
            animation:fadeSlide .5s .3s ease both;
        }
        .card-subtitle{
            text-align:center;font-size:.82rem;color:#7a9bba;font-weight:400;
            margin-bottom:2rem;
            animation:fadeSlide .5s .4s ease both;
        }
        @keyframes fadeSlide{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

        /* ═══ INPUTS ═══ */
        .field{margin-bottom:1rem;animation:fadeSlide .5s var(--d,.5s) ease both}
        .input-box{
            position:relative;
            background:rgba(255,255,255,.7);
            border:1.5px solid rgba(200,215,235,.6);
            border-radius:14px;
            transition:all .3s cubic-bezier(.4,0,.2,1);
            display:flex;align-items:center;
        }
        .input-box:focus-within{
            border-color:rgba(80,160,230,.5);
            box-shadow:0 0 0 4px rgba(80,160,230,.08),0 4px 12px rgba(80,160,230,.06);
            background:rgba(255,255,255,.9);
            transform:translateY(-1px);
        }
        .input-box .icon{
            display:flex;align-items:center;justify-content:center;
            padding:0 0 0 16px;color:#9bb5cc;transition:color .3s;flex-shrink:0;
        }
        .input-box:focus-within .icon{color:#4a9fd6}
        .input-box input{
            flex:1;padding:15px 16px 15px 12px;
            border:none;outline:none;background:transparent;
            font-family:inherit;font-size:.88rem;color:#1e3a5f;
        }
        .input-box input::placeholder{color:#a3bdd4}

        /* Password toggle */
        .pwd-toggle{
            display:flex;align-items:center;justify-content:center;
            padding:0 14px 0 0;cursor:pointer;
            background:none;border:none;color:#9bb5cc;
            transition:all .2s;border-radius:8px;
        }
        .pwd-toggle:hover{color:#4a9fd6}

        /* ═══ SUBMIT BUTTON ═══ */
        .btn-submit{
            width:100%;padding:15px;border:none;cursor:pointer;
            background:linear-gradient(135deg,#1a8fcb,#38bdf8,#06b6d4);
            background-size:200% 200%;
            animation:btnGradient 4s ease infinite,fadeSlide .5s .7s ease both;
            color:#fff;font-family:inherit;font-weight:700;font-size:.95rem;
            letter-spacing:.04em;
            border-radius:14px;
            display:flex;align-items:center;justify-content:center;gap:8px;
            position:relative;overflow:hidden;
            box-shadow:0 4px 18px rgba(26,143,203,.3);
            transition:all .3s cubic-bezier(.4,0,.2,1);
            margin-top:1.5rem;
        }
        @keyframes btnGradient{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
        .btn-submit:hover{
            transform:translateY(-2px);
            box-shadow:0 8px 30px rgba(26,143,203,.35);
        }
        .btn-submit:active{transform:translateY(0);box-shadow:0 4px 12px rgba(26,143,203,.25)}
        .btn-submit::after{
            content:'';position:absolute;top:0;left:-150%;width:80%;height:100%;
            background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);
            transform:skewX(-20deg);
        }
        .btn-submit:hover::after{animation:btnShimmer .8s ease forwards}
        @keyframes btnShimmer{0%{left:-150%}100%{left:150%}}

        /* ═══ ERROR ═══ */
        .error-alert{
            background:rgba(254,226,226,.8);border:1px solid rgba(252,165,165,.5);
            border-radius:14px;padding:12px 16px;margin-bottom:1.2rem;
            display:flex;align-items:flex-start;gap:10px;backdrop-filter:blur(8px);
            animation:fadeSlide .4s ease both;
        }
        .error-alert svg{width:18px;height:18px;color:#ef4444;flex-shrink:0;margin-top:2px}
        .error-alert p{font-size:.8rem;color:#b91c1c;font-weight:500;line-height:1.5}

        /* ═══ FOOTER ═══ */
        .card-footer{
            text-align:center;padding:.8rem 2rem 1.5rem;
            animation:fadeSlide .5s .8s ease both;
        }
        .card-footer span{
            font-size:.72rem;color:#9bb5cc;
            display:flex;align-items:center;justify-content:center;gap:6px;
        }
        .card-footer img{width:16px;height:16px;opacity:.5}

        /* ═══ LAYOUT ═══ */
        .login-wrapper{
            position:relative;z-index:1;
            min-height:100vh;display:flex;
            align-items:center;justify-content:center;
            padding:1rem;
        }
    </style>
</head>
<body>

    {{-- ═══ ANIMATED BACKGROUND ═══ --}}
    <div class="bg-scene">
        {{-- Animated rings in background --}}
        <div class="bg-rings">
            <div class="bg-ring bg-ring-1"></div>
            <div class="bg-ring bg-ring-2"></div>
            <div class="bg-ring bg-ring-3"></div>
            <div class="bg-ring bg-ring-4"></div>
            <div class="bg-ring bg-ring-5"></div>
            <div class="float-ring float-ring-1"></div>
            <div class="float-ring float-ring-2"></div>
            <div class="float-ring float-ring-3"></div>
            <div class="float-ring float-ring-4"></div>
        </div>

        {{-- Aurora blobs --}}
        <div class="aurora" style="width:600px;height:400px;top:-10%;left:-5%;background:rgba(140,210,255,.25);--dur:12s;--tx:60px;--ty:40px;--sc:1.1"></div>
        <div class="aurora" style="width:500px;height:350px;bottom:-5%;right:-8%;background:rgba(100,195,240,.2);--dur:15s;--tx:-50px;--ty:-30px;--sc:1.15"></div>
        <div class="aurora" style="width:300px;height:300px;top:30%;right:10%;background:rgba(170,220,255,.15);--dur:10s;--tx:-30px;--ty:20px;--sc:.95"></div>

        {{-- Light streaks --}}
        <div class="light-streak" style="top:25%;width:60%;--dur:8s;--delay:0s"></div>
        <div class="light-streak" style="top:55%;width:45%;--dur:10s;--delay:3s"></div>
        <div class="light-streak" style="top:75%;width:55%;--dur:9s;--delay:6s"></div>
        <div class="light-streak" style="top:40%;width:35%;--dur:11s;--delay:2s;transform:rotate(-3deg)"></div>

        {{-- Sparkles --}}
        <div class="sparkle" style="top:15%;left:20%;--s:6px;--dur:5s;--delay:0s;--ty:-15px"></div>
        <div class="sparkle" style="top:60%;left:75%;--s:4px;--dur:7s;--delay:1s;--ty:-20px"></div>
        <div class="sparkle" style="top:35%;left:50%;--s:5px;--dur:6s;--delay:2s;--ty:-12px"></div>
        <div class="sparkle" style="top:80%;left:30%;--s:3px;--dur:8s;--delay:3s;--ty:-18px"></div>
        <div class="sparkle" style="top:20%;left:85%;--s:5px;--dur:5.5s;--delay:.5s;--ty:-14px"></div>
        <div class="sparkle" style="top:70%;left:10%;--s:4px;--dur:6.5s;--delay:1.5s;--ty:-16px"></div>

        {{-- Flares --}}
        <div class="flare"></div>
        <div class="glow-tr"></div>
    </div>

    {{-- ═══ LOGIN CARD ═══ --}}
    <div class="login-wrapper">
        <div class="glass-card" x-data="{ showPwd: false }">
            <div class="card-body">

                {{-- Animated elements behind logo --}}
                <div class="logo-bg-shape shape-1"></div>
                <div class="logo-bg-shape shape-2"></div>
                <div class="logo-bg-shape shape-3"></div>
                
                {{-- Orbiting dots --}}
                <div class="orbit-container">
                    <div class="orbit-dot orbit-dot-1"></div>
                    <div class="orbit-dot orbit-dot-2"></div>
                    <div class="orbit-dot orbit-dot-3"></div>
                </div>

                {{-- Pulsing rings --}}
                <div class="logo-halo">
                    <div class="ring ring-1"></div>
                    <div class="ring ring-2"></div>
                    <div class="ring ring-3"></div>
                </div>

                {{-- Logo --}}
                <div class="logo-wrap">
                    <img src="{{ asset('branding/clinica-junical-logo.png') }}" alt="Clínica Junical">
                </div>

                {{-- Title --}}
                <h1 class="card-title">Bienvenido de nuevo</h1>
                <p class="card-subtitle">Ingresa para continuar al sistema</p>

                {{-- Errors --}}
                @if($errors->any())
                <div class="error-alert">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    <div>
                        @foreach($errors->all() as $e)
                        <p>{{ $e }}</p>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Status (mensaje tras recuperar contraseña) --}}
                @if(session('status'))
                <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:12px;padding:.75rem 1rem;margin-bottom:1rem;color:#065f46;font-size:.78rem;line-height:1.4">
                    {{ session('status') }}
                </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- User / Cédula --}}
                    <div class="field" style="--d:.45s">
                        <div class="input-box">
                            <div class="icon">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <input type="text" name="login" value="{{ old('login') }}" required autofocus
                                   placeholder="Usuario o cédula">
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="field" style="--d:.55s">
                        <div class="input-box">
                            <div class="icon">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <input :type="showPwd ? 'text' : 'password'" name="password" required
                                   placeholder="Contraseña">
                            <button type="button" @click="showPwd = !showPwd" class="pwd-toggle" tabindex="-1">
                                <svg x-show="!showPwd" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPwd" x-cloak width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn-submit">
                        Ingresar
                    </button>
                </form>

                <div style="margin-top:1rem;text-align:center">
                    <a href="{{ route('password.forgot') }}"
                       style="font-size:.78rem;font-weight:600;color:#2563eb;text-decoration:none">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
            </div>

            {{-- Footer --}}
            <div class="card-footer">
                <span>
                    <img src="{{ asset('branding/clinica-junical-icon.png') }}" alt="">
                    Clínica Junical · {{ date('Y') }}
                </span>
            </div>
        </div>
    </div>

</body>
</html>
