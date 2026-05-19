@extends('errors.layout')

@section('title', '404 — Page not found')

@section('content')
    <div class="code">404</div>
    <h1>Lost in the alumni network</h1>
    <p>The page you're looking for doesn't exist or has been moved.</p>
    <div class="actions">
        <a href="/" class="btn">Take me home</a>
        <a href="/directory" class="btn btn-ghost">Browse directory</a>
    </div>
@endsection
