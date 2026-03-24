@extends('adminlte::page')

@section('title', 'Home - Accounts')

@section('content_header')
    <div class="page-header-bar">
        <div class="page-header-left">
            <div class="page-header-info">
                <h1 class="page-header-title">Home</h1>
                <span class="page-header-sub">
                    <i class="fa fa-building mr-1"></i>
                    Select an account to continue
                </span>
            </div>
        </div>
        @if(auth()->user()->type == 1)
            <a href="{{ route('dashboard') }}" class="btn-change-branch">
                <i class="fa fa-tachometer-alt mr-2"></i>Dashboard
            </a>
        @endif
    </div>
@stop

@section('content')

    {!! Form::open(['method' => 'GET', 'route' => ['home'], 'id' => 'search_form']) !!}
    {!! Form::close() !!}

    <div class="branches-card">
        <div class="branches-card-header">
            <div class="section-label">
                <span class="section-label-text">Accounts</span>
                <div class="section-label-line"></div>
            </div>
            <div class="search-wrap">
                <i class="fa fa-search search-icon"></i>
                {!! Form::text('search', $search, [
                    'class'       => 'branch-search',
                    'placeholder' => 'Search accounts…',
                    'form'        => 'search_form',
                ]) !!}
                <button type="submit" form="search_form" class="search-btn">Go</button>
            </div>
        </div>

        <div class="branches-card-body">
            @if(!empty($accounts->total()))
                <div class="menu-grid">
                    @foreach($accounts as $account)
                        <a href="{{ route('branches', encrypt($account->account_id)) }}" class="menu-tile tile-blue">
                            <div class="tile-glow"></div>
                            <div class="tile-icon"><i class="fa fa-user"></i></div>
                            <div class="tile-content">
                                <span class="tile-title">{{ $account->short_name }}</span>
                                <span class="tile-desc">{{ $account->account_code }}</span>
                            </div>
                            <i class="fa fa-chevron-right tile-arrow"></i>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fa fa-building empty-icon"></i>
                    <p class="empty-text">No accounts available.</p>
                </div>
            @endif
        </div>

        <div class="branches-card-footer">
            {{ $accounts->links(data: ['scrollTo' => false]) }}
        </div>
    </div>

@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
@stop

@section('js')
@stop
