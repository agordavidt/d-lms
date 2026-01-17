@extends('layouts.admin')

@section('title', 'My Profile')
@section('breadcrumb-parent', 'Settings')
@section('breadcrumb-current', 'Profile')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Profile Information -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 font-weight-bold">
                    <i class="icon-user text-primary mr-2"></i>Profile Information
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('learner.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Avatar Upload -->
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <img src="{{ $user->avatar_url }}" 
                                alt="{{ $user->name }}" 
                                class="rounded-circle"
                                width="120" 
                                height="120"
                                id="avatarPreview">
                            <label for="avatar" class="btn btn-sm btn-primary rounded-circle position-absolute" 
                                style="bottom: 0; right: 0; cursor: pointer;">
                                <i class="icon-camera"></i>
                            </label>
                            <input type="file" 
                                id="avatar" 
                                name="avatar" 
                                class="d-none" 
                                accept="image/*"
                                onchange="previewAvatar(event)">
                        </div>
                        <p class="text-muted small mt-2 mb-0">Click camera icon to change photo</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-semibold mb-2">First Name</label>
                            <input type="text" 
                                name="first_name" 
                                class="form-control @error('first_name') is-invalid @enderror" 
                                value="{{ old('first_name', $user->first_name) }}"
                                required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="font-weight-semibold mb-2">Last Name</label>
                            <input type="text" 
                                name="last_name" 
                                class="form-control @error('last_name') is-invalid @enderror" 
                                value="{{ old('last_name', $user->last_name) }}"
                                required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="font-weight-semibold mb-2">Email Address</label>
                        <input type="email" 
                            name="email" 
                            class="form-control @error('email') is-invalid @enderror" 
                            value="{{ old('email', $user->email) }}"
                            required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="font-weight-semibold mb-2">Phone Number</label>
                        <input type="tel" 
                            name="phone" 
                            class="form-control @error('phone') is-invalid @enderror" 
                            value="{{ old('phone', $user->phone) }}"
                            placeholder="+234 XXX XXX XXXX">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="icon-check mr-2"></i>Save Changes
                    </button>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 font-weight-bold">
                    <i class="icon-lock text-warning mr-2"></i>Change Password
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('learner.profile.password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="font-weight-semibold mb-2">Current Password</label>
                        <input type="password" 
                            name="current_password" 
                            class="form-control @error('current_password') is-invalid @enderror" 
                            required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="font-weight-semibold mb-2">New Password</label>
                        <input type="password" 
                            name="password" 
                            class="form-control @error('password') is-invalid @enderror" 
                            required>
                        <small class="text-muted">Minimum 8 characters, with uppercase, lowercase, numbers, and symbols</small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="font-weight-semibold mb-2">Confirm New Password</label>
                        <input type="password" 
                            name="password_confirmation" 
                            class="form-control" 
                            required>
                    </div>

                    <button type="submit" class="btn btn-warning btn-lg">
                        <i class="icon-lock mr-2"></i>Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function previewAvatar(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
}
</script>
@endpush