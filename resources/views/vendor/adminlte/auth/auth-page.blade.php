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
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&display=swap" rel="stylesheet">
    <style>
        html, body {
            height: 100vh !important;
            margin: 0;
            overflow: hidden;
        }

        .login-page, .register-page {
            background-color: #f4f6f9;
            display: flex;
            justify-content: flex-end;
            align-items: stretch;
            height: 100vh;
        }

        .login-box, .register-box {
            margin: 0 !important;
            width: 100% !important;
            max-width: 400px;
        }


        .auth-container-split {
            width: 100%;
            max-width: 500px;
            background: rgba(255, 255, 255, 0.7) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px;
            box-shadow: -5px 0 15px rgba(0,0,0,0.1);
        }

        .auth-container-split .login-logo {
            margin-bottom: 2rem;
            text-align: center;
        }

        .auth-container-split .card {
            box-shadow: none;
            border: none;
            background: transparent;
        }

        .text-portal {
            height: 50px;
        }

        @media (max-width: 767.98px) {
            .auth-container-split {
                max-width: 100% !important;
                padding: 1.5rem;
                background: rgba(255, 255, 255, 0.7) !important;
            }

            .login-page, .register-page {
                justify-content: center !important; /* Center the content area */
            }
        }
    </style>
@stop

@section('classes_body'){{ ($auth_type ?? 'login') . '-page' }}@stop

@section('body')
    <div class="auth-container-split">
        <div class="{{ $auth_type ?? 'login' }}-box">

            {{-- Logo --}}
            <div class="{{ $auth_type ?? 'login' }}-logo">
                <a href="{{ $dashboard_url }}" class="text-dark" style="font-weight: 900; font-size: 50px;">
                    {!! config('adminlte.logo', 'STO') !!}
                </a>
            </div>

            {{-- Card Box --}}
            <div class="card {{ config('adminlte.classes_auth_card', '') }}">
                @hasSection('auth_header')
                    <div class="card-header">
                        <h3 class="card-title float-none text-center">@yield('auth_header')</h3>
                    </div>
                @endif

                <div class="card-body {{ $auth_type ?? 'login' }}-card-body">
                    @yield('auth_body')
                </div>

                @hasSection('auth_footer')
                    <div class="card-footer">
                        @yield('auth_footer')
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
