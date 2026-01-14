@extends('layouts.admin')

@section('title', 'Learner Dashboard')
@section('page-title', 'My Dashboard')
@section('page-subtitle', 'Welcome, ' . auth()->user()->name)

@section('content')
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-tools display-1 text-muted mb-3"></i>
        <h3>Learner Dashboard Coming Soon</h3>
        <p class="text-muted">Your learning portal will be available here.</p>
    </div>
</div>
@endsection