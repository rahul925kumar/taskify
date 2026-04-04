<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>@yield('title', 'Task Manager') | TechnoByte Developers</title>
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
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    @stack('styles')
</head>
<body>
    <div class="wrapper">
        {{-- Sidebar --}}
        @include('partials.sidebar')

        <div class="main-panel">
            {{-- Header/Navbar --}}
            @include('partials.navbar')

            <div class="container">
                <div class="page-inner">
                    @yield('content')
                </div>
            </div>

            @include('partials.footer')
        </div>
    </div>

    <script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('assets/js/kaiadmin.min.js') }}"></script>

    <script>
        function showToast(message, type) {
            var icons = { success: 'fa fa-check-circle', error: 'fa fa-times-circle', warning: 'fa fa-exclamation-triangle', info: 'fa fa-info-circle' };
            var colors = { success: '#31ce36', error: '#f25961', warning: '#ffad46', info: '#48abf7' };
            $.notify({ icon: icons[type] || icons.info, message: message }, {
                type: type === 'error' ? 'danger' : type,
                placement: { from: 'top', align: 'right' },
                delay: 4000,
                timer: 1000,
                z_index: 9999,
                animate: { enter: 'animated fadeInDown', exit: 'animated fadeOutUp' },
                template:
                    '<div data-notify="container" class="col-xs-11 col-sm-4 alert alert-{0}" role="alert" style="border-left: 4px solid ' + (colors[type] || colors.info) + '; box-shadow: 0 4px 15px rgba(0,0,0,0.15);">' +
                        '<button type="button" aria-hidden="true" class="close" data-notify="dismiss" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);opacity:.7;font-size:18px;">×</button>' +
                        '<span data-notify="icon" class="me-2" style="font-size:20px;vertical-align:middle;"></span> ' +
                        '<span data-notify="message" style="vertical-align:middle;">{2}</span>' +
                    '</div>'
            });
        }

        @if(session('success'))  showToast(@json(session('success')), 'success');  @endif
        @if(session('error'))    showToast(@json(session('error')), 'error');      @endif
        @if(session('warning'))  showToast(@json(session('warning')), 'warning');  @endif
        @if(session('info'))     showToast(@json(session('info')), 'info');        @endif

        @if($errors->any())
            @foreach($errors->all() as $err)
                showToast(@json($err), 'error');
            @endforeach
        @endif
    </script>

    @stack('scripts')
</body>
</html>
