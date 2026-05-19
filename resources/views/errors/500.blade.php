@extends('errors.layout')

@section('title', '500 — Something went wrong')

@section('content')
    <div class="code">500</div>
    <h1>Something went wrong</h1>
    <p>We've been notified. Try refreshing the page, or come back in a few minutes.</p>
    <div class="actions">
        <button class="btn" onclick="window.location.reload()">Reload page</button>
        <a href="/" class="btn btn-ghost">Go home</a>
    </div>
@endsection
