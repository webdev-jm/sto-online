@extends('adminlte::page')

@section('title', 'Branches - '.$account->short_name)

@section('content_header')
    <div class="page-header-bar">
        <div class="page-header-left">
            <div class="page-header-badge">{{ $account->account_code }}</div>
            <div class="page-header-info">
                <h1 class="page-header-title">{{ $account->short_name }}</h1>
                <span class="page-header-sub">
                    <i class="fa fa-store mr-1"></i>
                    Select a branch to continue
                </span>
            </div>
        </div>
        <a href="/home" class="btn-change-branch">
            <i class="fa fa-user mr-2"></i>Change Account
        </a>
    </div>
@stop

@section('content')

    {!! Form::open(['method' => 'GET', 'route' => ['home'], 'id' => 'search_form']) !!}
    {!! Form::close() !!}

    <div class="branches-card">
        <div class="branches-card-header">
            <div class="section-label">
                <span class="section-label-text">Branches</span>
                <div class="section-label-line"></div>
            </div>
            <div class="search-wrap">
                <i class="fa fa-search search-icon"></i>
                {!! Form::text('search', $search, [
                    'class'       => 'branch-search',
                    'placeholder' => 'Search branches…',
                    'form'        => 'search_form',
                ]) !!}
                <button type="submit" form="search_form" class="search-btn">Go</button>
            </div>
        </div>

        <div class="branches-card-body">
            <div class="menu-grid">
                @forelse($branches as $branch)
                    <a href="{{ route('menu', encrypt($branch->id)) }}" class="menu-tile tile-yellow">
                        <div class="tile-glow"></div>
                        <div class="tile-icon"><i class="fa fa-store"></i></div>
                        <div class="tile-content">
                            <span class="tile-title">{{ $branch->code }}</span>
                            <span class="tile-desc">{{ $branch->name }}</span>
                        </div>
                        <i class="fa fa-chevron-right tile-arrow"></i>
                    </a>
                @empty
                    <div class="empty-state">
                        <i class="fa fa-store-slash empty-icon"></i>
                        <p class="empty-text">No branches found.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="branches-card-footer">
            {{ $branches->links() }}
        </div>
    </div>

@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
@stop

@section('js')
@stop
