@extends('layouts.admin')

@section('title', $program->name)
@section('breadcrumb-parent', 'Programs')
@section('breadcrumb-current', 'No Cohorts Available')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-body text-center py-5">
                <div style="font-size: 80px; margin-bottom: 2rem;">📅</div>
                <h3 class="mb-3">No Cohorts Available</h3>
                <p class="text-muted mb-4">
                    There are currently no available cohorts for <strong>{{ $program->name }}</strong>.
                    New cohorts will be announced soon!
                </p>
            </div>
        </div>

        <!-- Program Info -->
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="mb-3">About This Program</h5>
                <p class="mb-3">{{ $program->description }}</p>
                
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <strong>Duration:</strong> {{ $program->duration }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Price:</strong> ₦{{ number_format($program->discounted_price, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Notify Form -->
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="mb-3">Get Notified</h5>
                <p class="mb-4">Enter your email to be notified when new cohorts are available for this program.</p>
                
                <form onsubmit="event.preventDefault(); alert('Thank you! We will notify you when new cohorts are available.');">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" placeholder="Your email address" 
                               value="{{ auth()->user()->email }}" required>
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">Notify Me</button>
                        </div>
                    </div>
                    <small class="text-muted">
                        We'll send you an email as soon as new cohorts are announced.
                    </small>
                </form>
            </div>
        </div>

        <div class="text-center mt-3">
            <a href="{{ route('learner.programs.index') }}" class="btn btn-outline-primary">
                Browse Other Programs
            </a>
        </div>
    </div>
</div>
@endsection