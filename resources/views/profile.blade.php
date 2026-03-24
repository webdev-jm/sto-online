@extends('adminlte::page')

@section('title', 'Profile')

@section('content_header')
    <h1>PROFILE</h1>
@stop

@section('content')

    <div class="row">

        <div class="col-lg-4">

            <!-- User Details -->
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                            src="{{auth()->user()->adminlte_image()}}"
                            alt="User profile picture">
                    </div>

                    <h3 class="profile-username text-center">{{auth()->user()->name}}</h3>

                    <p class="text-muted text-center">{{implode(', ', auth()->user()->getRoleNames()->toArray())}}</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item p-1">
                            <b>Username</b>
                            <span class="float-right">{{auth()->user()->username ?? '-'}}</span>
                        </li>
                        <li class="list-group-item p-1">
                            <b>Email</b>
                            <span class="float-right">{{auth()->user()->email ?? '-'}}</span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>

        <div class="col-lg-8">

            <div class="card card-primary card-outline">
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                      <li class="nav-item"><a class="nav-link active" href="#settings" data-toggle="tab">Profile Settings</a></li>
                    <li class="nav-item"><a class="nav-link" href="#activity" data-toggle="tab">Activity</a></li>
                    <li class="nav-item"><a class="nav-link" href="#change-password" data-toggle="tab">Change Password</a></li>
                  </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="active tab-pane" id="settings">
                            <livewire:user.profile-settings />
                        </div>
                        <div class="tab-pane" id="activity">
                            <livewire:activity-logs.user-logs />
                        </div>
                        <div class="tab-pane" id="change-password">
                            <livewire:user.change-password />
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <style>
        /* Profile Box - Frosted Glass Effect */
        .box-profile {
            background: var(--glass-light) !important; /* Translucent white */
            border-radius: var(--radius-sm) !important; /* Match Apple-style corners */
            padding: 20px !important;
            border: 1px solid var(--glass-border) !important;
            box-shadow: var(--shadow-btn) !important; /* Subtle inner shadow */
        }

        /* Ensure the parent card is also glass or transparent to see the effect */
        .card.card-primary.card-outline {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }

        /* ─── NAVIGATION PILLS (Tabs) ──────────────────────────────── */
        .nav-pills .nav-link {
            font-family: "Syne", sans-serif !important;
            font-weight: 700 !important;
            font-size: 0.75rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            border-radius: var(--radius-xs) !important;
            color: rgba(255, 255, 255, 0.87) !important;
            transition: all 0.2s ease;
            margin-right: 5px;
        }

        .nav-pills .nav-link.active {
            background: var(--col-accent-g) !important; /* Blue-Purple Gradient */
            color: #fff !important;
            box-shadow: var(--shadow-btn) !important;
        }

        .nav-pills .nav-link:not(.active):hover {
            background: rgba(0, 0, 0, 0.05) !important;
            color: var(--col-dark) !important;
        }

        .dark-mode .nav-pills .nav-link:not(.active) {
            color: #a1a1a6 !important;
        }

        /* Dark Mode support for the box-profile */
        .dark-mode .box-profile {
            background: rgba(28, 28, 30, 0.6) !important; /* Apple Dark Material style */
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #f5f5f7 !important;
        }

        /* Typography consistency with your design system */
        .profile-username {
            font-family: "Syne", sans-serif !important;
            font-weight: 700 !important;
            letter-spacing: -0.02em;
        }

        .profile-user-img {
            border: 3px solid var(--glass-border) !important;
            box-shadow: var(--shadow-btn) !important;
        }

        /* Make list items inside the glass box transparent */
        .box-profile .list-group-item {
            background: transparent !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
        }

        .dark-mode .box-profile .list-group-item {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
    </style>
@stop

@section('js')
@stop
