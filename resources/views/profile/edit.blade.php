@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h4 class="mb-4"><i class="fas fa-user me-2"></i>{{ __('Profile') }}</h4>

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    @if (session('status') === 'profile-updated')
                        {{ __('Profile information updated successfully.') }}
                    @elseif (session('status') === 'password-updated')
                        {{ __('Password updated successfully.') }}
                    @else
                        {{ session('status') }}
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Update Profile Information --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>{{ __('Profile Information') }}</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">{{ __("Update your account's profile information and email address.") }}</p>

                    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                        @csrf
                    </form>

                    <form method="post" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Name') }}</label>
                            <input 
                                id="name" 
                                name="name" 
                                type="text" 
                                class="form-control @error('name') is-invalid @enderror" 
                                value="{{ old('name', $user->name) }}" 
                                required 
                                autofocus 
                                autocomplete="name"
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Email') }}</label>
                            <input 
                                id="email" 
                                name="email" 
                                type="email" 
                                class="form-control @error('email') is-invalid @enderror" 
                                value="{{ old('email', $user->email) }}" 
                                required 
                                autocomplete="username"
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                <div class="mt-2">
                                    <p class="text-sm text-muted">
                                        {{ __('Your email address is unverified.') }}
                                        <button form="send-verification" class="btn btn-link p-0 text-decoration-none">
                                            {{ __('Click here to re-send the verification email.') }}
                                        </button>
                                    </p>

                                    @if (session('status') === 'verification-link-sent')
                                        <p class="mt-2 text-success">
                                            {{ __('A new verification link has been sent to your email address.') }}
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>{{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Update Password --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-key me-2"></i>{{ __('Update Password') }}</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>

                    <form method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="mb-3">
                            <label for="update_password_current_password" class="form-label">{{ __('Current Password') }}</label>
                            <div class="input-group">
                                <input 
                                    id="update_password_current_password" 
                                    name="current_password" 
                                    type="password" 
                                    class="form-control {{ $errors->updatePassword->has('current_password') ? 'is-invalid' : '' }}" 
                                    autocomplete="current-password"
                                >
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('update_password_current_password', 'toggle_current_password')">
                                    <i class="fas fa-eye" id="toggle_current_password"></i>
                                </button>
                            </div>
                            @if ($errors->updatePassword->has('current_password'))
                                <div class="invalid-feedback d-block">{{ $errors->updatePassword->first('current_password') }}</div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="update_password_password" class="form-label">{{ __('New Password') }}</label>
                            <div class="input-group">
                                <input 
                                    id="update_password_password" 
                                    name="password" 
                                    type="password" 
                                    class="form-control {{ $errors->updatePassword->has('password') ? 'is-invalid' : '' }}" 
                                    autocomplete="new-password"
                                >
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('update_password_password', 'toggle_password')">
                                    <i class="fas fa-eye" id="toggle_password"></i>
                                </button>
                            </div>
                            @if ($errors->updatePassword->has('password'))
                                <div class="invalid-feedback d-block">{{ $errors->updatePassword->first('password') }}</div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="update_password_password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                            <div class="input-group">
                                <input 
                                    id="update_password_password_confirmation" 
                                    name="password_confirmation" 
                                    type="password" 
                                    class="form-control {{ $errors->updatePassword->has('password_confirmation') ? 'is-invalid' : '' }}" 
                                    autocomplete="new-password"
                                >
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('update_password_password_confirmation', 'toggle_password_confirmation')">
                                    <i class="fas fa-eye" id="toggle_password_confirmation"></i>
                                </button>
                            </div>
                            @if ($errors->updatePassword->has('password_confirmation'))
                                <div class="invalid-feedback d-block">{{ $errors->updatePassword->first('password_confirmation') }}</div>
                            @endif
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>{{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endsection
