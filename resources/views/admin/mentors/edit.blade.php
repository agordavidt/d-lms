@extends('layouts.admin')
@section('title', 'Edit Mentor')

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb">
            <a href="{{ route('admin.mentors.index') }}">Mentors</a> /
            <a href="{{ route('admin.mentors.show', $mentor->id) }}">{{ $mentor->first_name }} {{ $mentor->last_name }}</a>
        </div>
        <h1>Edit Mentor</h1>
    </div>
</div>

<div class="container section">
<div style="max-width: 520px;">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.mentors.update', $mentor->id) }}">
                @csrf @method('PUT')

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">First Name <span style="color:var(--error)">*</span></label>
                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                               value="{{ old('first_name', $mentor->first_name) }}" required>
                        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name <span style="color:var(--error)">*</span></label>
                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                               value="{{ old('last_name', $mentor->last_name) }}" required>
                        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email <span style="color:var(--error)">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $mentor->email) }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $mentor->phone) }}">
                </div>

                <div style="border-top: 1px solid var(--border); margin: 1.25rem 0; padding-top: 1.25rem;">
                    <div class="form-hint" style="margin-bottom: 1rem;">Leave password fields blank to keep the current password.</div>

                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem;">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('admin.mentors.show', $mentor->id) }}" class="btn btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endsection