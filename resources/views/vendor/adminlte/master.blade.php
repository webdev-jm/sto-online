<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    {{-- Base Meta Tags --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Custom Meta Tags --}}
    @yield('meta_tags')

    {{-- Title --}}
    <title>
        @yield('title_prefix', config('adminlte.title_prefix', ''))
        @yield('title', config('adminlte.title', 'AdminLTE 3'))
        @yield('title_postfix', config('adminlte.title_postfix', ''))
    </title>

    {{-- Custom stylesheets (pre AdminLTE) --}}
    @yield('adminlte_css_pre')

    {{-- Extra Configured Plugins Stylesheets --}}
    @include('adminlte::plugins', ['type' => 'css'])

    {{-- Base Stylesheets --}}
    @if(!config('adminlte.enabled_laravel_mix'))
        <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">

        @if(config('adminlte.google_fonts.allowed', true))
            <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
        @endif
    @else
        <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_mix_css_path', 'css/app.css')) }}">
    @endif

    {{-- Livewire Styles --}}
    @if(config('adminlte.livewire'))
        @if(app()->version() >= 7)
            @livewireStyles
        @else
            <livewire:styles />
        @endif
    @endif

    {{-- Custom Stylesheets (post AdminLTE) --}}
    @yield('adminlte_css')

    {{-- Favicon --}}
    @if(config('adminlte.use_ico_only'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
    @elseif(config('adminlte.use_full_favicon'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicons/apple-icon-57x57.png') }}">
        <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicons/apple-icon-60x60.png') }}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicons/apple-icon-72x72.png') }}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicons/apple-icon-76x76.png') }}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicons/apple-icon-114x114.png') }}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicons/apple-icon-120x120.png') }}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicons/apple-icon-144x144.png') }}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicons/apple-icon-152x152.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-icon-180x180.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicons/favicon-96x96.png') }}">
        <link rel="icon" type="image/png" sizes="192x192"  href="{{ asset('favicons/android-icon-192x192.png') }}">
        <link rel="manifest" crossorigin="use-credentials" href="{{ asset('favicons/manifest.json') }}">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="{{ asset('favicon/ms-icon-144x144.png') }}">
    @endif

    @laravelPWA

</head>

<body class="@yield('classes_body'){{Auth::user() && auth()->user()->dark_mode ? ' dark-mode' : ' '}}" @yield('body_data')>

    {{-- Body Content --}}
    @yield('body')

    {{-- Base Scripts --}}
    @if(!config('adminlte.enabled_laravel_mix'))
        <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
        <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
        <script src="{{ asset('js/app.js') }}"></script>
    @else
        <script src="{{ mix(config('adminlte.laravel_mix_js_path', 'js/app.js')) }}"></script>
    @endif
    {{-- Extra Configured Plugins Scripts --}}
    @include('adminlte::plugins', ['type' => 'js'])

    {{-- Livewire Script --}}
    @if(config('adminlte.livewire'))
        @if(app()->version() >= 7)
            @livewireScripts
        @else
            <livewire:scripts />
        @endif
    @endif

    {{-- Custom Scripts --}}
    @yield('adminlte_js')

    <script>
        /**
        * modal-fix.js
        * ------------
        * Fixes the Bootstrap / AdminLTE issue where `.modal-backdrop` is appended
        * to <body> AFTER `.modal`, causing it to paint on top of the modal dialog
        * despite a lower z-index — because both are `position:fixed` children of
        * the same stacking context (<body>), and DOM order is used as the tiebreaker.
        *
        * Strategy: use a MutationObserver to watch <body> for new children.
        * Whenever a `.modal-backdrop` appears, immediately move it to just BEFORE
        * its corresponding `.modal` in the DOM so z-index works as expected.
        *
        * Drop this script at the bottom of <body>, after Bootstrap JS.
        * No jQuery required, no framework dependency.
        */

        (function () {
            "use strict";

            /**
            * Move every existing/new .modal-backdrop to sit just before
            * the first .modal it belongs to.
            */
            function reorderBackdrops() {
                const backdrops = document.querySelectorAll(".modal-backdrop");

                backdrops.forEach(function (backdrop) {
                    // Find the modal that is currently shown (or any modal on the page)
                    const modal =
                        document.querySelector(".modal.show") ||
                        document.querySelector(".modal");

                    if (!modal) return;

                    // Only move if the backdrop currently comes AFTER the modal in the DOM.
                    // Node.compareDocumentPosition returns a bitmask:
                    //   DOCUMENT_POSITION_FOLLOWING (4) means `modal` comes after `backdrop`
                    //   DOCUMENT_POSITION_PRECEDING (2) means `modal` comes before `backdrop`
                    const position = backdrop.compareDocumentPosition(modal);
                    const modalIsAfterBackdrop = position & Node.DOCUMENT_POSITION_FOLLOWING;

                    if (modalIsAfterBackdrop) {
                        // Already correct — backdrop is before modal, nothing to do.
                        return;
                    }

                    // Backdrop is after modal — move backdrop to just before the modal.
                    modal.parentNode.insertBefore(backdrop, modal);
                });
            }

            // Run once on load to catch any pre-existing bad ordering.
            document.addEventListener("DOMContentLoaded", reorderBackdrops);

            // Watch for Bootstrap dynamically appending the backdrop to <body>.
            const observer = new MutationObserver(function (mutations) {
                let needsReorder = false;

                mutations.forEach(function (mutation) {
                    mutation.addedNodes.forEach(function (node) {
                        if (
                        node.nodeType === 1 && // Element node
                        (node.classList.contains("modal-backdrop") ||
                            node.classList.contains("modal"))
                        ) {
                            needsReorder = true;
                        }
                    });
                });

                if (needsReorder) {
                    // Use requestAnimationFrame so Bootstrap finishes its own DOM work first.
                    requestAnimationFrame(reorderBackdrops);
                }
            });

            observer.observe(document.body, { childList: true });
        })();
    </script>

    <script>
        $(function() {
            // Dark mode toggle
            $('#darkModeToggle').on('click', function(e) {
                e.preventDefault();
                $('body').toggleClass('dark-mode');
                $(this).find('i').toggleClass('fa-moon').toggleClass('fa-sun');
                $('body').find('.content-wrapper').toggleClass('bg-light').toggleClass('bg-dark', !$('body').find('.content-wrapper').hasClass('bg-light'));
                $('body').find('.main-header').toggleClass('navbar-white navbar-light').toggleClass('navbar-secondary navbar-dark', !$('body').find('.main-header').hasClass('navbar-secondary navbar-dark'));
            });

            // close modal
            $('body').on('click', '[data-dismiss="modal"]', function () {
                $(this).closest('.modal').modal('hide');
            });

        });
    </script>

</body>

</html>
