<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BOSCHMA Facility Staff Login">
    <meta name="keywords" content="BOSCHMA, Healthcare, Facility Login">
    <title>Facility Staff Login - BOSCHMA</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/img/brand/favicon.png') }}" type="image/png">

    <!-- Bootstrap CSS -->
    <link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="{{ asset('assets/plugins/icons/feather/feather.css') }}" rel="stylesheet">

    <!-- App CSS -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #01542B 0%, #0a7e4a 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            margin: 20px;
        }

        .login-left {
            background: linear-gradient(135deg, #01542B 0%, #0a7e4a 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-right {
            padding: 60px 40px;
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo img {
            width: 80px;
            height: auto;
        }

        .login-title {
            font-size: 28px;
            font-weight: 700;
            color: #01542B;
            margin-bottom: 10px;
        }

        .login-subtitle {
            color: #6c757d;
            margin-bottom: 40px;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #01542B;
            box-shadow: 0 0 0 0.2rem rgba(1, 84, 43, 0.1);
        }

        .btn-login {
            background: linear-gradient(135deg, #01542B 0%, #0a7e4a 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(1, 84, 43, 0.2);
            color: white;
        }

        .admin-link {
            text-align: center;
            margin-top: 30px;
            color: #6c757d;
        }

        .admin-link a {
            color: #01542B;
            text-decoration: none;
            font-weight: 600;
        }

        .admin-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .login-left {
                display: none;
            }

            .login-right {
                padding: 40px 30px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="row g-0 h-100">
            <!-- Left Side -->
            <div class="col-lg-6">
                <div class="login-left">
                    <div class="logo mb-4">
                        <img src="{{ asset('assets/img/brand/logo.png') }}" alt="BOSCHMA Logo">
                    </div>
                    <h2 class="mb-3">Welcome to BOSCHMA</h2>
                    <h4 class="mb-4">Facility Staff Portal</h4>
                    <p class="mb-4">
                        Access your facility dashboard to manage beneficiaries, enrollments, and healthcare services.
                    </p>
                    <div class="mt-auto">
                        <p class="small mb-0">
                            <strong>Borno State Contributory Healthcare Management Agency</strong>
                        </p>
                        <p class="small">
                            Providing quality healthcare for all citizens
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Side -->
            <div class="col-lg-6">
                <div class="login-right">
                    <div class="logo d-lg-none mb-4">
                        <img src="{{ asset('assets/img/brand/logo.png') }}" alt="BOSCHMA Logo">
                    </div>

                    <h3 class="login-title">Facility Staff Login</h3>
                    <p class="login-subtitle">Enter your credentials to access the portal</p>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <i class="fe fe-alert-circle me-2"></i>
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('facility.login.submit') }}">
                        @csrf

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fe fe-mail text-muted"></i>
                                </span>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email') }}"
                                    placeholder="Enter your email" required autocomplete="email" autofocus>
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">
                                    <i class="fe fe-alert-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fe fe-lock text-muted"></i>
                                </span>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" placeholder="Enter your password" required
                                    autocomplete="current-password">
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">
                                    <i class="fe fe-alert-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mb-4">
                            <button type="submit" class="btn btn-login">
                                <i class="fe fe-log-in me-2"></i>Sign In
                            </button>
                        </div>
                    </form>

                    <div class="admin-link">
                        Are you a BOSCHMA administrator?
                        <a href="{{ route('login') }}">Login here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/style.js') }}"></script>
</body>

</html>
