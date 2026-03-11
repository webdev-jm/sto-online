@extends('adminlte::master')

@section('meta_tags')
<link rel="shortcut icon" href="{{asset('/images/STO ONLINE.png')}}" type="image/x-icon">
@endsection

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@section('adminlte_css')
    @stack('css')
    @yield('css')

    <style>
        .pagination {
            margin-bottom: 3px;
        }
        .pagination .page-link {
            padding:5px 9px 5px 9px;
        }
        .card-title {
            font-weight: 750;
        }

        /* .bg-dark {
            background: #ffffff8e url('/images/pexels-hngstrm-inverted.jpg') no-repeat center center;
            background-size: cover;
            backdrop-filter: blur(5px);
        }

        .bg-light {
            background: #ffffff8e url('/images/pexels-hngstrm-2341290.jpg') no-repeat center center;
            background-size: cover;
            backdrop-filter: blur(5px);
        } */

        .bg-dark, .bg-light {
            position: relative;
            overflow: hidden; /* Keeps the blur from bleeding outside the edges */
            z-index: 1;
        }

        .bg-dark::before, .bg-light::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            filter: blur(3px); /* This is the actual blur */
            transform: scale(1.1); /* Prevents white edges caused by the blur */
            z-index: -1;
        }

        .bg-dark::before {
            background-image: url('/images/pexels-hngstrm-inverted.jpg');
            background-color: #ffffff8e;
        }

        .bg-light::before {
            background-image: url('/images/pexels-hngstrm-2341290.jpg');
            background-color: #ffffff8e;
        }

        .text-portal {
            height: 20px;
        }

        .bg-dark hr {
            background-color: white !important;
            opacity: .5;
        }
    </style>
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
    <div class="wrapper">

        {{-- Preloader Animation --}}
        @if(config('adminlte.preloader.enabled'))
            @include('adminlte::partials.common.preloader')
        @endif

        {{-- Top Navbar --}}
        @if($layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.navbar.navbar-layout-topnav')
        @else
            @include('adminlte::partials.navbar.navbar')
        @endif

        {{-- Left Main Sidebar --}}
        @if(!$layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.sidebar.left-sidebar')
        @endif

        {{-- Content Wrapper --}}
        @empty($iFrameEnabled)
            @include('adminlte::partials.cwrapper.cwrapper-default')
        @else
            @include('adminlte::partials.cwrapper.cwrapper-iframe')
        @endempty

        {{-- Footer --}}
        {{-- @hasSection('footer')
            @include('adminlte::partials.footer.footer')
        @endif --}}
        <footer class="main-footer">
            <div class="text-center">
                <b>Copyright © {{date('Y')}} <a href="https://www.bevi.com.ph/" target="_blank">BEVI</a>. All rights reserved</b>
            </div>
        </footer>

        {{-- Right Control Sidebar --}}
        @if(config('adminlte.right_sidebar'))
            @include('adminlte::partials.sidebar.right-sidebar')
        @endif

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
