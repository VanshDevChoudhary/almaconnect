@extends('errors.layout')

@section('title', '503 — Back soon')

@section('content')
    <div class="code">503</div>
    <h1>Back soon</h1>
    <p>We're updating the platform. This should only take a few minutes — try again shortly.</p>
    <div class="actions">
        <button class="btn" onclick="window.location.reload()">Try again</button>
    </div>
@endsection
