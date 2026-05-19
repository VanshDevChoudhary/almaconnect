@extends('errors.layout')

@section('title', '419 — Session expired')

@section('content')
    <div class="code">419</div>
    <h1>Your session has expired</h1>
    <p>For security reasons, your session timed out. Please log in again to continue.</p>
    <div class="actions">
        <a href="/login" class="btn">Log in</a>
    </div>
@endsection
