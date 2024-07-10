@extends('adminlte::master')

@php( $dashboard_url = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home') )

@if (config('adminlte.use_route_url', false))
    @php( $dashboard_url = $dashboard_url ? route($dashboard_url) : '' )
@else
    @php( $dashboard_url = $dashboard_url ? url($dashboard_url) : '' )
@endif

@section('adminlte_css')
    @stack('css')
    @yield('css')
    <style>
        .image-container-gif {
            position: relative;
            width: max-content; /* Adjust as necessary */
            height: max-content; /* Adjust as necessary */
            z-index: -1;
        }
        .overlapping-image {
            position: absolute;
            top: -90px;
            left: -30px;
        }

        .overlapping-image:nth-child(2) {
            top: -85px;
            left: -40px;
        }

        .overlapping-image:nth-child(3) {
            top: -70px;
            left: -22px;
            width: 250px;
        }
    </style>
@stop

@section('classes_body'){{ ($auth_type ?? 'login') . '-page' }}@stop

@section('body')
    <div class="{{ $auth_type ?? 'login' }}-box">

        <div class="image-container-gif">
            <img src="{{asset('/images/X5NX.gif')}}" alt="" class="overlapping-image">
            <img src="{{asset('/images/XDZT.gif')}}" alt="" class="overlapping-image">
            {{-- <img src="{{asset('/images/YlW9.gif')}}" alt="" class="overlapping-image"> --}}
        </div>

        {{-- Logo --}}
        <div class="{{ $auth_type ?? 'login' }}-logo">
            <a href="{{ $dashboard_url }}" class="text-white" style="font-weight: 900; font-size: 50px;">
                {{-- Logo Label --}}
                {{-- {!! config('adminlte.logo', 'STO') !!} --}}
                <b><span class="text-red">BEV</span> PORTAL</b>
            </a>
        </div>

        {{-- Card Box --}}
        <div class="card {{ config('adminlte.classes_auth_card', 'card-outline card-secondary') }}">

            {{-- Card Header --}}
            @hasSection('auth_header')
                <div class="card-header {{ config('adminlte.classes_auth_header', '') }}">
                    <h3 class="card-title float-none text-center">
                        @yield('auth_header')
                    </h3>
                </div>
            @endif

            {{-- Card Body --}}
            <div class="card-body pb-2 {{ $auth_type ?? 'login' }}-card-body {{ config('adminlte.classes_auth_body', '') }}">
                @yield('auth_body')
            </div>

            {{-- Card Footer --}}
            @hasSection('auth_footer')
                <div class="card-footer {{ config('adminlte.classes_auth_footer', '') }}">
                    @yield('auth_footer')
                </div>
            @endif

        </div>

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
