@extends('layouts.admin')
@section('title', 'Add Mentor')

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('admin.mentors.index') }}">Mentors</a></div>
        <h1>Add Mentor</h1>
    </div>
</div>

<div class="container section">
<div style="max-width: 520px;">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.mentors.store') }}">
                @csrf

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">First Name <span style="color:var(--error)">*</span></label>
                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                               value="{{ old('first_name') }}" required>
                        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name <span style="color:var(--error)">*</span></label>
                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                               value="{{ old('last_name') }}" required>
                        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email <span style="color:var(--error)">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Password <span style="color:var(--error)">*</span></label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    <div class="form-hint">Min. 8 characters. Mentor can change it after first login.</div>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password <span style="color:var(--error)">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
                    <button type="submit" class="btn btn-primary">Create Mentor</button>
                    <a href="{{ route('admin.mentors.index') }}" class="btn btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endsection


