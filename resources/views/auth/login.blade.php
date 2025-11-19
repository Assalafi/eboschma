<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport">
    <title>Login - BOSCHMA Management Portal</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ url('assets/img/brand/favicon.ico') }}" type="image/x-icon" />

    <!-- Bootstrap css-->
    <link id="style" href="{{ url('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />

    <!-- Icons css-->
    <link href="{{ url('assets/plugins/web-fonts/icons.css') }}" rel="stylesheet" />
    <link href="{{ url('assets/plugins/web-fonts/font-awesome/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ url('assets/plugins/web-fonts/plugin.css') }}" rel="stylesheet" />

    <!-- Style css-->
    <link href="{{ url('assets/css/style.css') }}" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: url('{{ asset('assets/img/brand/bg-img2.png') }}') center center / cover no-repeat fixed;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(ellipse at center, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.6) 40%, rgba(255, 255, 255, 0.9) 70%, rgba(255, 255, 255, 1) 100%);
            backdrop-filter: blur(2px);
            z-index: 0;
        }

        .main-wrapper {
            position: relative;
            z-index: 1;
            min-height: 100vh;
        }

        .header-section {
            background: white;
            padding: 20px 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-logo {
            width: 80px;
            height: 80px;
            flex-shrink: 0;
        }

        .header-text {
            flex: 1;
            text-align: center;
        }

        .header-text h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-text p {
            margin: 5px 0 0 0;
            font-size: 16px;
            color: #7f8c8d;
            font-weight: 500;
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 140px);
            padding: 40px 20px;
        }

        .login-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            max-width: 420px;
            width: 100%;
        }

        .login-header {
            background: #2980b9;
            color: white;
            padding: 20px;
            font-size: 24px;
            font-weight: 600;
        }

        .login-body {
            padding: 30px;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 20px;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 18px;
        }

        .input-group-custom input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .input-group-custom input:focus {
            outline: none;
            border-color: #2980b9;
            box-shadow: 0 0 0 3px rgba(41, 128, 185, 0.1);
        }

        .login-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .forgot-link {
            color: #2980b9;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-login {
            background: #27ae60;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
        }

        .alert {
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 4px;
        }

        @media (max-width: 768px) {
            .header-section {
                padding: 15px 20px;
            }

            .header-content {
                flex-direction: column;
                text-align: center;
            }

            .header-logo {
                width: 60px;
                height: 60px;
            }

            .header-text h1 {
                font-size: 18px;
            }

            .header-text p {
                font-size: 14px;
            }

            .login-body {
                padding: 20px;
            }

            .login-footer {
                flex-direction: column;
                gap: 15px;
            }

            .btn-login {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <!-- Loader -->
    <div id="global-loader">
        <img src="{{ url('assets/img/loader.svg') }}" class="loader-img" alt="Loader">
    </div>
    <!-- End Loader -->

    <div class="main-wrapper">
        <!-- Header Section -->
        <div class="header-section">
            <div class="header-content">
                <img src="{{ url('assets/img/brand/logo.png') }}" alt="Borno State Logo" class="header-logo">
                <div class="header-text">
                    <h1>BORNO STATE CONTRIBUTORY HEALTHCARE MANAGEMENT AGENCY</h1>
                    <p>Management Portal</p>
                </div>
            </div>
        </div>

        <!-- Login Section -->
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    Welcome
                </div>
                <div class="login-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 list-unstyled">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ url('/login') }}" method="POST">
                        @csrf

                        <div class="input-group-custom">
                            <i class="fa fa-envelope input-icon"></i>
                            <input type="email" name="email" placeholder="Enter your email or id" required>
                        </div>

                        <div class="input-group-custom">
                            <i class="fa fa-key input-icon"></i>
                            <input type="password" name="password" placeholder="Your password" required>
                        </div>

                        <div class="login-footer">
                            <a href="#" class="forgot-link">Forgot password?</a>
                            <button type="submit" class="btn-login">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End Page -->

    <!-- Jquery js-->
    <script src="{{ url('assets/plugins/jquery/jquery.min.js') }}"></script>

    <!-- Bootstrap js-->
    <script src="{{ url('assets/plugins/bootstrap/js/popper.min.js') }}"></script>
    <script src="{{ url('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>

    <!-- Custom js-->
    <script src="{{ url('assets/js/custom.js') }}"></script>

    <!-- Loader js-->
    <script>
        $(window).on("load", function() {
            $("#global-loader").fadeOut("slow");
        })
    </script>
</body>

</html>
