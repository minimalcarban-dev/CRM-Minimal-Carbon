@extends('layouts.auth')

@section('title', 'Admin Login')

@push('head')
    <style>
        :root {
            --accent: #1e88e5;
            --accent-2: #6dd5ed
        }

        body {
            background: linear-gradient(180deg, #f6f9fc 0%, #ffffff 100%);
        }

        .auth-card {
            display: flex;
            box-shadow: 0 10px 30px rgba(16, 24, 40, 0.08);
            border-radius: 12px;
            overflow: hidden
        }

        .auth-visual {
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: white;
            padding: 40px;
            flex: 0 0 44%;
            position: relative
        }

        .auth-visual h2 {
            font-weight: 700
        }

        .auth-visual p {
            opacity: 0.95
        }

        .auth-form {
            padding: 36px 32px;
            flex: 1
        }

        .form-floating-icon {
            position: relative
        }

        .form-floating-icon .bi {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa7b2
        }

        .form-floating-icon input {
            padding-left: 38px
        }

        .btn-accent {
            background: linear-gradient(90deg, var(--accent), #2fb1f0);
            border: none
        }

        .btn-accent:hover {
            filter: brightness(0.95)
        }

        /* entrance animation */
        .animate-up {
            animation: upIn .6s cubic-bezier(.2, .9, .2, 1) both
        }

        @keyframes upIn {
            from {
                opacity: 0;
                transform: translateY(12px) scale(.995)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        /* floating circles */
        .float-circle {
            position: absolute;
            border-radius: 50%;
            opacity: .12;
            filter: blur(6px)
        }

        .c1 {
            width: 120px;
            height: 120px;
            right: -30px;
            top: -20px;
            background: #ffffff
        }

        .c2 {
            width: 80px;
            height: 80px;
            left: -30px;
            bottom: -20px;
            background: #000000
        }

        .small-note {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, .9)
        }

        @media(max-width:767px) {
            .auth-card {
                flex-direction: column
            }

            .auth-visual {
                flex-basis: unset;
                padding: 24px
            }
        }
    </style>
@endpush

@section('content')
    <div class="animate-up">
        <div class="auth-card mx-auto" style="max-width:900px;">
            <div class="auth-visual d-flex flex-column justify-content-center">
                <div class="float-circle c1"></div>
                <div class="float-circle c2" style="opacity:.04"></div>
                <div class="px-2">
                    <h2>Welcome Back</h2>
                    <p class="small-note">Sign in to manage admins, permissions and orders. Your actions are secured and
                        audited.</p>
                    <div class="mt-4">
                        <small class="d-block">Quick tips</small>
                        <ul class="mb-0" style="padding-left:1rem">
                            <li style="font-size:.95rem">Use a strong password</li>
                            <li style="font-size:.95rem">Keep your session secure</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="auth-form bg-white">
                <div class="mb-3 text-center">
                    <img src="/uploads/logo.png" alt="logo" style="height:44px;object-fit:contain;"
                        onerror="this.style.display='none'">
                </div>
                <h4 class="mb-1">Super Admin Login</h4>
                <p class="text-muted">Enter your credentials to access the admin panel.</p>

                <form method="POST" action="{{ route('admin.login.post') }}" class="mt-3">
                    @csrf

                    <div class="mb-3 form-floating-icon">
                        <i class="bi bi-envelope-fill"></i>
                        <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}"
                            required>
                    </div>

                    <div class="mb-3 form-floating-icon position-relative">
                        <i class="bi bi-lock-fill"></i>
                        <input id="pw" type="password" name="password" class="form-control" placeholder="Password" required>
                        <button type="button" id="togglePw" class="btn btn-sm btn-light position-absolute"
                            style="right:8px;top:50%;transform:translateY(-50%);">Show</button>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                            <label class="form-check-label small" for="remember">Remember me</label>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-accent btn-lg">Login</button>
                    </div>
                </form>

                <div class="text-center mt-3 small text-muted">Need help? Contact your system administrator.</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const pw = document.getElementById('pw');
            const btn = document.getElementById('togglePw');
            if (btn && pw) {
                btn.addEventListener('click', function () {
                    if (pw.type === 'password') { pw.type = 'text'; btn.textContent = 'Hide'; }
                    else { pw.type = 'password'; btn.textContent = 'Show'; }
                });
            }
            // small entrance stagger
            document.querySelectorAll('.animate-up').forEach((el) => {
                el.style.opacity = 0; el.style.transform = 'translateY(12px)';
                requestAnimationFrame(() => { el.style.transition = 'all .45s cubic-bezier(.2,.9,.2,1)'; el.style.opacity = 1; el.style.transform = 'none'; });
            });
        })();
    </script>
@endpush