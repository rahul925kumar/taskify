<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>@yield('title', 'Login - Task Manager') | TechnoByte Developers</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('assets/img/kaiadmin/favicon.ico') }}" type="image/x-icon" />

    <script src="{{ asset('assets/js/plugin/webfont/webfont.min.js') }}"></script>
    <script>
        WebFont.load({
            google: { families: ["Public Sans:300,400,500,600,700"] },
            custom: {
                families: ["Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"],
                urls: ["{{ asset('assets/css/fonts.min.css') }}"],
            },
            active: function () { sessionStorage.fonts = true; },
        });
    </script>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/plugins.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/kaiadmin.min.css') }}" />
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { background: #fff; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); max-width: 450px; width: 100%; padding: 40px; }
        .auth-card .logo { text-align: center; margin-bottom: 30px; }
        .auth-card .logo img { height: 35px; }
        .auth-card h4 { text-align: center; font-weight: 600; margin-bottom: 5px; }
        .auth-card p.subtitle { text-align: center; color: #8d9498; margin-bottom: 25px; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="logo">
            <img src="{{ asset('assets/img/kaiadmin/logo_custom.webp') }}" alt="Task Manager" height="40" />
            <p style="margin-top: 8px; font-size: 11px; color: #adb5bd; letter-spacing: 1px; text-transform: uppercase;">Powered by TechnoByte Developers</p>
        </div>
        @yield('content')
    </div>

    <script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    <script>
        function showToast(message, type) {
            var icons = { success: 'fa fa-check-circle', error: 'fa fa-times-circle', warning: 'fa fa-exclamation-triangle', info: 'fa fa-info-circle' };
            $.notify({ icon: icons[type] || icons.info, message: message }, {
                type: type === 'error' ? 'danger' : type,
                placement: { from: 'top', align: 'center' },
                delay: 4000,
                timer: 1000,
                z_index: 9999,
                animate: { enter: 'animated fadeInDown', exit: 'animated fadeOutUp' },
            });
        }

        @if(session('success'))  showToast(@json(session('success')), 'success');  @endif
        @if(session('error'))    showToast(@json(session('error')), 'error');      @endif
        @if(session('warning'))  showToast(@json(session('warning')), 'warning');  @endif

        @if($errors->any())
            @foreach($errors->all() as $err)
                showToast(@json($err), 'error');
            @endforeach
        @endif
    </script>

    @stack('scripts')
</body>
</html>
