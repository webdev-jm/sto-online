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

        .bg-dark {
            background: #ffffff8e url('/images/pexels-hngstrm-inverted.jpg') no-repeat center center;
            background-size: cover;
        }
        .bg-light {
            background: #ffffff8e url('/images/pexels-hngstrm-2341290.jpg') no-repeat center center;
            background-size: cover;
        }
    </style>
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
    <div class="wrapper">

        {{-- Preloader Animation --}}
        @if($layoutHelper->isPreloaderEnabled())
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
                <b>Copyright © 2023 <a href="https://www.bevi.com.ph/" target="_blank">BEVI</a>. All rights reserved</b>
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
