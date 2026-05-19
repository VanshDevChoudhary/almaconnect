@extends('errors.layout')

@section('title', '403 — Access denied')

@section('content')
    <div class="code">403</div>
    <h1>Access denied</h1>
    <p>You don't have permission to see this page.</p>
    <div class="actions">
        <a href="/dashboard" class="btn">Back to dashboard</a>
    </div>
@endsection
